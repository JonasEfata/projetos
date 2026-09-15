# Relatório: Separação da partição /home do sistema (dual boot Ubuntu + Windows)

**Data:** 15/09/2026
**Sistema:** Ubuntu (dual boot com Windows), disco ST1000LM035-1RK172 (1 TB), GUID Partition Table
**Objetivo:** Mover o diretório `/home` para uma partição própria (`/dev/sda8`, 411 GB Ext4), separando os dados pessoais do sistema operacional.

---

## Contexto

O notebook está em dual boot com Windows, e o disco já possuía várias partições (EFI, Windows, recovery, raiz do Ubuntu em `/dev/sda7`). Foi criada uma nova partição (`/dev/sda8`, ext4, 411 GB) com o objetivo de usá-la como `/home` dedicado — prática comum para preservar dados pessoais em caso de reinstalação do sistema.

## Passo a passo executado

1. **Montagem temporária da nova partição:**
   ```
   sudo mkdir /mnt/newhome
   sudo mount /dev/sda8 /mnt/newhome
   ```

2. **Cópia dos dados do /home atual para a nova partição:**
   ```
   sudo rsync -aAXv /home/ /mnt/newhome/
   ```
   Preserva permissões, ACLs, atributos estendidos e symlinks.

3. **Edição do fstab para montar a nova partição em /home no boot:**
   ```
   sudo nano /etc/fstab
   ```
   Adicionada a linha:
   ```
   UUID=<uuid_da_sda8> /home ext4 defaults 0 2
   ```

4. **Substituição do /home antigo pelo novo ponto de montagem:**
   ```
   sudo umount /mnt/newhome
   sudo mv /home /home_old
   sudo mkdir /home
   sudo reboot
   ```

## Falha encontrada

Após o reboot, o sistema não conseguia localizar a pasta `/home` do usuário (login travava por falta do diretório pessoal).

### Diagnóstico

Entrando em **modo de recuperação** (GRUB → Advanced options → Recovery mode → root shell), foram feitas as seguintes verificações:

- `blkid /dev/sda8` revelou o UUID real da partição: `3d7b5666-9df1-45f8-925b-a5c4255fd4b5`.
- O UUID digitado manualmente no `/etc/fstab` estava **incorreto**, com caracteres trocados (`45f6` em vez de `45f8`, `923b` em vez de `925b`, etc.) — provável erro de transcrição.
- Corrigido o UUID, o `mount -a` não retornou erro, mas o `/home` continuava vazio.
- Uma segunda inspeção do `/etc/fstab` (`cat /etc/fstab`) revelou a causa real: a linha estava comentada com um `#` no início:
  ```
  #UUID=3d7b5666-9df1-45f8-925b-a5c4255fd4b5 /home ext4 defaults 0 2
  ```
  Por estar comentada, o systemd/mount ignorava a linha por completo — daí o `/home` continuar vazio mesmo sem erro de digitação.

### Correção final

1. Removido o `#` no início da linha no `/etc/fstab`.
2. Executado `systemctl daemon-reload` (necessário pois o mount avisou que o fstab havia sido modificado, mas o systemd ainda usava a versão em cache).
3. Reexecutado `mount -a`.
4. Confirmado com:
   ```
   mount | grep sda8
   ls -la /home
   ```
   Resultado: `/dev/sda8 on /home type ext4 (rw,relatime)`, com a pasta do usuário e `lost+found` presentes.
5. Reiniciado o sistema (`reboot`) — boot e login funcionaram normalmente, usando a partição dedicada como `/home`.

## Causas-raiz identificadas

1. **UUID digitado manualmente com erro de transcrição** — vários caracteres trocados ao copiar/colar ou digitar o UUID no fstab.
2. **Linha comentada por engano** (`#` residual no início da linha), que mascarou o problema mesmo após a correção do UUID, já que `mount -a` não retorna erro para uma linha comentada — ela simplesmente é ignorada.

## Lições aprendidas

- Sempre conferir o UUID real com `blkid /dev/sdaX` **antes** de editar o fstab, e idealmente copiar o valor diretamente da saída do comando em vez de digitar manualmente.
- Após qualquer edição do fstab, testar com `sudo mount -a` **antes** de reiniciar. Isso evita boots quebrados.
- Um `mount -a` sem erro não garante que a montagem ocorreu — é necessário confirmar com `mount | grep <dispositivo>` e `ls` no ponto de montagem.
- Manter o `/home` antigo como backup (`/home_old`) durante o processo foi uma boa prática: garantiu que nenhum dado fosse perdido enquanto o problema era diagnosticado.
- O modo de recuperação do GRUB (Advanced options → Recovery mode → root shell) foi essencial para editar o fstab com o sistema ainda "vivo" (sem precisar de live USB), já que o boot normal não completava.

## Resultado final

A partição `/dev/sda8` está corretamente montada em `/home`, com todos os dados do usuário preservados via rsync. O sistema agora possui `/home` separado do sistema operacional (raiz em `/dev/sda7`), permitindo reinstalações futuras do Ubuntu sem risco de perda de dados pessoais — desde que a partição `/home` não seja formatada durante uma futura instalação.

**Próximo passo recomendado:** após alguns dias de uso normal do sistema, confirmando que nada está faltando, remover a pasta de backup `/home_old` da partição raiz para liberar espaço.
