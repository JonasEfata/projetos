# Criando um pendrive de boot do macOS a partir do Linux (Ubuntu)

**Caso real:** MacBook Pro 13" A1278 (Early 2011), sem acesso a outro Mac, sem Internet Recovery funcional, usando apenas um notebook com Ubuntu em dual boot.

Este relato documenta o processo completo — incluindo os erros pelo caminho — e termina com um guia limpo e reproduzível.

---

## 1. Contexto e diagnóstico inicial

- Hardware: MacBook Pro A1278, identificado pelo número de série (`C02F6UHJDH2G`), decodificado como fabricação no 1º semestre de 2011 → **MacBookPro8,1**.
- Sistema original instalado: **macOS High Sierra 10.13.6**.
- Problema: o Mac não iniciava de forma alguma — nem o sistema, nem o modo de recuperação (Internet Recovery via `Cmd+R`) conseguia contatar os servidores da Apple.
- Recursos disponíveis: nenhum outro Mac, apenas um notebook próprio com **Ubuntu em dual boot com Windows**.
- Decisão: já que o Mac não bootava em lugar nenhum, não havia "sistema original" a preservar — o objetivo virou simplesmente **recuperar o Mac com um sistema funcional**, via pendrive de instalação criado manualmente no Linux.

### Causa raiz real do problema (identificada depois, via inspeção física)

Ao longo do processo, o pendrive de instalação foi refeito várias vezes e mais de uma tentativa travou de forma inexplicável durante o boot/instalação, mesmo com o pendrive corretamente preparado. A causa real só foi identificada por inspeção física do hardware: **o SSD interno do MacBook Pro estava com defeito**. Ele foi substituído por outro SSD usado, em bom estado, o que resolveu definitivamente os travamentos.

Isso é importante para quem for reproduzir este processo: **se a instalação travar de forma persistente mesmo com um pendrive corretamente criado e testado, considere testar/substituir o disco interno do Mac antes de continuar depurando o processo do pendrive.** Nem todo travamento tem origem no instalador — pode ser o armazenamento de destino.

## 2. Primeira tentativa — macOS El Capitan (10.11)

Como é o sistema mais leve e mais fácil de obter via link direto da Apple, a primeira tentativa foi com o instalador `InstallMacOSX.dmg` do El Capitan.

### Obstáculos encontrados (e o que causou cada um)

| Problema | Causa | Solução |
|---|---|---|
| Download feito no PC errado | Um primeiro download rodou no computador do amigo (Windows), que não pode processar `.dmg` | Reiniciar o download direto no Ubuntu |
| `.dmg` não abre no Windows | `.dmg` é formato exclusivo do macOS | Confirmar que todo o processo deve rodar em Linux ou Mac, nunca Windows puro |
| Arquivo baixado incompleto (`ls -la` mostrando tamanho menor que o esperado) | Copiar o arquivo pela interface gráfica **antes** do download terminar | Sempre confirmar 100% do download antes de mover/copiar o arquivo |
| `mount: tipo de sistema de arquivos incorreto` | Módulo `hfsplus` do kernel não carregado, ou tentativa de montar um `.dmg`/`.img` com tabela de partição interna direto (sem `kpartx`) | `sudo modprobe hfsplus` + usar `kpartx -av` para mapear partições internas antes de montar |
| `sgdisk`: *"Secondary partition table overlaps..."* / *"not saving changes"* | Pendrive com resíduos de tabela de partição antiga (MBR + GPT misturados) | `sudo sgdisk -Z /dev/sdX` (zerar completamente) antes de reparticionar |
| `cp: erro de escrita... Não há espaço disponível no dispositivo` mesmo com pendrive de 29GB | A partição foi criada com `dd` copiando uma imagem pequena (~2GB) **por cima** de uma partição grande — o cabeçalho do sistema de arquivos "herdou" o tamanho pequeno original | Nunca usar `dd` de uma partição pequena sobre uma maior. Reformatar do zero com `mkfs.hfsplus` no tamanho real do disco, e copiar arquivos com `rsync`, não com `dd` |
| `rsync`: `get_xattr_data... osx.com.apple.FinderInfo... failed` | O Linux não consegue ler certos atributos estendidos específicos do HFS+/macOS (usados para marcar a "bootabilidade") | Rodar `rsync` com `--no-xattrs --no-acls` para pular esse atributo (não impede o funcionamento do instalador) |
| `Packages` sumiu / erro "diretório inexistente" | A pasta `System/Installation/Packages` do sistema base é originalmente um **link simbólico**, e apagá-la com `rm -rf` sem recriar como pasta real quebra a estrutura | Recriar a pasta com `mkdir -p` antes de copiar os pacotes reais para dentro dela |
| Instalação do El Capitan travou por várias horas no boot do Mac | Causa real descoberta depois: **SSD interno do Mac com defeito** (não era problema do pendrive ou do processo) | Substituição do SSD por outro usado, em bom estado — ver seção "Causa raiz real" acima |

### Decisão de troca

Após a instalação do El Capitan travar (mais de 5 horas parada), decidiu-se abortar e refazer todo o processo com **macOS Sierra (10.12)**, versão mais próxima do sistema original (High Sierra) e ainda disponível via link direto da Apple.

## 3. Segunda tentativa — macOS Sierra (10.12), bem-sucedida

Com as lições da primeira tentativa, o processo rodou de forma muito mais direta e sem repetir os mesmos erros.

### Link oficial usado para download

```
http://updates-http.cdn-apple.com/2019/cert/061-39476-20191023-48f365f4-0015-4c41-9f44-39d3d2aca067/InstallOS.dmg
```//
(Disponibilizado publicamente pela própria Apple em: https://support.apple.com/en-us/102662 — seção "Use a web browser for older versions")

### Resultado final

Pendrive de 15GB formatado, com ~11GB ocupados (73% de uso), contendo:
- `Applications/`, `Library/`, `System/` (sistema base completo)
- `Install macOS Sierra.app`
- `InstallESD.dmg`, `BaseSystem.dmg`, `BaseSystem.chunklist` (arquivos de instalação)
- `System/Installation/Packages/` com os pacotes reais do sistema

Boot bem-sucedido no MacBook Pro segurando **Option (⌥)** ao ligar, e **instalação concluída com sucesso**.

---

## 4. Guia reproduzível (processo limpo, sem os erros)

Use este roteiro caso precise repetir o processo em outra máquina ou situação parecida.

### Pré-requisitos

- Um computador com **Linux** (testado em Ubuntu)
- Um pendrive de **pelo menos 16GB** (será completamente apagado)
- Conexão à internet estável para o download (~5-6GB dependendo da versão do macOS)

### Passo 1 — Instalar as ferramentas necessárias

```bash
sudo apt update
sudo apt install dmg2img hfsprogs p7zip-full gdisk kpartx rsync
```

### Passo 2 — Baixar o instalador oficial da Apple

Acesse a página oficial e escolha a versão desejada:
`https://support.apple.com/en-us/102662` (seção "Use a web browser for older versions")

Confirme que o download terminou 100% antes de prosseguir:

```bash
ls -la ~/Downloads/InstallOS.dmg   # ou InstallMacOSX.dmg, dependendo da versão
```

### Passo 3 — Extrair o instalador

```bash
cd ~/Downloads
7z x InstallOS.dmg -o"macos_extract"
```

Aguarde a mensagem **"Everything is Ok"** antes de continuar.

### Passo 4 — Localizar os arquivos-chave

```bash
find ~/Downloads/macos_extract -iname "BaseSystem.dmg"
find ~/Downloads/macos_extract -iname "InstallESD.dmg"
find ~/Downloads/macos_extract -type d -iname "Packages"
```

Anote os caminhos completos retornados — serão usados nos passos seguintes.

### Passo 5 — Identificar o pendrive

Conecte o pendrive e rode:

```bash
lsblk
```

⚠️ Identifique com certeza qual dispositivo é o pendrive (ex: `/dev/sdb`) antes de continuar. Todos os comandos seguintes **apagam o disco selecionado**.

### Passo 6 — Particionar e formatar o pendrive

```bash
sudo umount /dev/sdX1 2>/dev/null
sudo sgdisk -Z /dev/sdX
sudo sgdisk -o /dev/sdX
sudo sgdisk -n 1:0:0 -t 1:AF00 -c 1:"OS X Base System" /dev/sdX
sudo mkfs.hfsplus -v "OS X Base System" /dev/sdX1
sudo mkdir -p /mnt/usb
sudo mount /dev/sdX1 /mnt/usb
```

(substitua `sdX` pelo device correto identificado no passo anterior)

### Passo 7 — Converter e montar o BaseSystem.dmg

```bash
cd <pasta_onde_está_o_BaseSystem.dmg>
dmg2img BaseSystem.dmg BaseSystem.img
sudo mkdir -p /mnt/basesystem
sudo kpartx -av BaseSystem.img
```

Anote o nome do loop device retornado (ex: `loop34p1`).

### Passo 8 — Copiar o sistema base para o pendrive

```bash
sudo mount /dev/mapper/loopXXp1 /mnt/basesystem
sudo rsync -rlptD --no-xattrs --no-acls /mnt/basesystem/ /mnt/usb/
```

(troque `loopXXp1` pelo nome real do passo anterior)

### Passo 9 — Recriar a pasta de pacotes e copiar os arquivos reais

```bash
sudo rm -rf /mnt/usb/System/Installation/Packages
sudo mkdir -p /mnt/usb/System/Installation/Packages
sudo cp -a <caminho_da_pasta_Packages_encontrada_no_passo_4>/. /mnt/usb/System/Installation/Packages/
sudo cp -a <caminho_do_InstallESD.dmg>/InstallESD.dmg /mnt/usb/
sudo cp -a <caminho_do_BaseSystem.dmg>/BaseSystem.dmg /mnt/usb/
sudo cp -a <caminho_do_BaseSystem.chunklist>/BaseSystem.chunklist /mnt/usb/
```

### Passo 10 — Confirmar e desmontar

```bash
df -h /mnt/usb
ls /mnt/usb

sudo umount /mnt/usb
sudo umount /mnt/basesystem
sudo kpartx -d BaseSystem.img
```

### Passo 11 — Boot no Mac

1. Conecte o pendrive no Mac
2. Desligue completamente
3. Ligue segurando **Option (⌥ / Alt)** por 10-15 segundos
4. Selecione o ícone do pendrive na tela de boot (geralmente aparece como "EFI Boot")
5. Aguarde o carregamento do instalador (pode levar alguns minutos em hardware antigo)

---

## 5. Principais lições aprendidas

1. **Nunca copiar arquivos grandes pela interface gráfica enquanto o download ainda está em andamento** — sempre confirmar 100% antes de mover.
2. **`dd` de uma imagem pequena sobre uma partição grande "engana" o sistema de arquivos quanto ao tamanho real disponível.** Prefira formatar do zero e copiar arquivos com `rsync`.
3. **`rsync --no-xattrs --no-acls`** evita erros com atributos específicos do HFS+/macOS que o Linux não processa corretamente.
4. **`Packages` dentro de `System/Installation/` deve ser recriado como pasta real** após removido, não presumido como já existente.
5. **Zerar completamente a tabela de partição (`sgdisk -Z`)** antes de reparticionar evita conflitos residuais de formatações anteriores no pendrive.
6. Em caso de travamento persistente na instalação, **trocar de versão do macOS ou de pendrive** costuma ser mais rápido do que tentar diagnosticar a causa raiz.
7. **Nem todo travamento é culpa do pendrive ou do processo de instalação.** Neste caso, a causa real dos travamentos era um **SSD interno defeituoso** no Mac — só identificado por inspeção física do hardware. Se um pendrive corretamente criado (testado e íntegro) continuar travando a instalação, vale testar o disco de destino antes de continuar depurando o processo de criação do instalador.

---

## 6. Estimativa de tempo investido

| Etapa | Tempo estimado |
|---|---|
| Diagnóstico inicial (identificação do modelo, decodificação do número de série) | ~30-40 min |
| Download e primeiras tentativas de extração do instalador | ~1h |
| Particionamento, formatação e primeiras tentativas de gravação do pendrive | ~1h30 |
| Nova tentativa (El Capitan) e identificação de estrutura de arquivos | ~30 min |
| Instalação travada (El Capitan, antes da descoberta do SSD defeituoso) | ~5h (tempo parado, não ativo) |
| Diagnóstico físico do hardware e substituição do SSD | não quantificado com precisão — inclui obtenção da peça usada |
| Reinício completo do processo com Sierra, até sucesso final | ~3h |

**Total estimado de esforço ativo (preparação de pendrive + diagnóstico): 6-7 horas**, distribuídas ao longo de **5 dias corridos**.

**Total estimado incluindo tempo de espera e trabalho físico no hardware: 10-15 horas.**

Esse tipo de reparo — hardware antigo (2011), sem acesso a outro Mac, com defeito de disco não diagnosticado inicialmente — é representativo do tempo que um processo manual via Linux pode exigir quando comparado a um reparo com ferramentas Apple nativas disponíveis.

---

*Documento gerado a partir de um processo real de recuperação de um MacBook Pro 13" (2011) sem acesso a hardware Apple adicional, usando exclusivamente ferramentas open source no Linux.*
