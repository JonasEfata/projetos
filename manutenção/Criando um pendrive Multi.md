# Criando um pendrive Multi-Boot com Ventoy a partir do Linux (Ubuntu)

**Caso real:** Criação de unidade USB bootável universal (Ventoy) com imagens ISO de instalação para suporte técnico, utilizando terminal Linux.

Este relatório documenta a instalação do Ventoy em um pendrive USB e a adição de imagens ISO de instalação do Windows 10.

---

## 1. Contexto e Ambiente

- **Sistema Operacional:** Ubuntu (Linux)
- **Ferramenta:** Ventoy 1.1.16 (Linux tar.gz)
- **Dispositivo de Destino:** Pendrive `/dev/sdb`
- **ISO Utilizada:** `Win10_22H2_BrazilianPortuguese_x64v1.iso`
- **Objetivo:** Ter um pendrive multi-boot atualizável por simples cópia de arquivos (sem necessidade de regravar o dispositivo a cada nova ISO).

---

## 2. Processo de Instalação e Execução

### Passo 1 — Download e Extração
Download da versão oficial do Ventoy para Linux e extração do pacote `.tar.gz` no diretório de Downloads:

```bash
cd ~/Downloads
tar -zxvf ventoy-1.1.16-linux.tar.gz
cd ventoy-1.1.16


sudo umount /dev/sdb*

sudo ./Ventoy2Disk.sh -i /dev/sdb

Create partitions on /dev/sdb by parted in MBR style ...
Done
Wait for partitions ...
partition exist OK
create efi fat fs /dev/sdb2 ...
mkfs.fat 4.2 (2021-01-31)
success
Wait for partitions $vPART1 and$vPART2 ...
/dev/sdb1 exist OK
/dev/sdb2 exist OK
partition exist OK
Format partition 1 /dev/sdb1 ...
mkexfatfs 1.3.0
Creating... done.
Flushing... done.
File system created successfully.
mkexfatfs success
writing data to disk ...
sync data ...
esp partition processing ...

Install Ventoy to /dev/sdb successfully finished.
