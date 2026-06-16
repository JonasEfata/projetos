# 📖 Efatá Book — Relatório de Progresso
**Projeto:** Loja Virtual Efatá Book  
**Site:** [efatabook.com.br](https://efatabook.com.br)  
**Período:** Maio — Junho 2026  
**Documento:** Versão 1.0  
**Missão:** Amor Pela Palavra — venda de Bíblias ACF, livros e eBooks cristãos

---

## 🎯 Objetivo do Projeto

Estruturar e colocar em operação a loja virtual da **Efatá Book** no WordPress + WooCommerce, com foco em:

- Venda de Bíblias ACF (principalmente da SBTB)
- Venda de livros e eBooks cristãos
- Entrega digital automática de eBooks
- Pagamento via PIX e cartão de crédito
- Notificações automáticas de venda por e-mail
- Suporte ao **Projeto Amor Pela Palavra**

---

## 🛠️ Ambiente Técnico

| Componente | Tecnologia |
|---|---|
| Hospedagem | HostGator |
| CMS | WordPress 7.0 |
| E-commerce | WooCommerce 10.8.1 |
| Tema | Kadence (Child Theme) |
| Page Builder | Spectra |
| Pagamento | Mercado Pago |
| E-mail SMTP | Brevo (via WP Mail SMTP) |

---

## ✅ O Que Foi Feito — Passo a Passo

### 1. Criação e Configuração da Página da Loja

**Problema encontrado:**  
A URL `/product/` retornava erro 404. Não havia página de loja configurada no WooCommerce.

**O que foi feito:**  
- Criada a página **"Loja"** no WordPress (`Páginas → Adicionar nova`)
- Configurada como página oficial da loja em `WooCommerce → Configurações → Produtos → Exibição → Página da loja`
- Adicionada ao menu de navegação do site

**Resultado:**  
✅ Loja acessível em `efatabook.com.br/loja/`

---

### 2. Organização dos Produtos

**Problema encontrado:**  
- 82 produtos cadastrados no total
- 72 produtos sem imagem publicados na loja
- Produtos sem imagem prejudicavam a apresentação e confiança da loja
- Preço incorreto: Bíblia ACF de Púlpito cadastrada a R$ 2,49 (correto: R$ 2.490,00)
- Categorias incorretas: cada produto havia sido criado como sua própria categoria

**O que foi feito:**  
- Produtos sem imagem movidos para **Rascunho** via edição em massa
- Preço da Bíblia ACF de Púlpito corrigido para R$ 2.490,00
- Fichas completas criadas para os produtos prioritários (título, descrição curta, descrição completa, SEO, slug, categoria, tags, alt text)
- Imagens verificadas e aprovadas para upload (proporção 800x800px — padrão WooCommerce)

**Resultado:**  
✅ Loja exibindo apenas produtos com imagem  
✅ Preços corrigidos  
✅ Produtos com descrições profissionais e otimizadas para SEO

---

### 3. Cadastro dos eBooks de Jonas Vieira

**Produtos cadastrados:**

| Título | Slug | Categoria |
|---|---|---|
| O Justo: Deus Procura e o Mundo Precisa — eBook | `ebook-o-justo` | eBooks Cristãos |
| Degrau por Degrau: Lições no Caminho — eBook | `ebook-degrau-por-degrau` | eBooks Cristãos |
| Homens da Graça: Amizades do Passado que Influenciam o Presente — eBook | `ebook-homens-da-graca` | eBooks Cristãos |

**Configuração técnica aplicada:**  
- Produtos marcados como **Virtual** e **Produto para download**
- Arquivos PDF vinculados em `Dados do produto → Download`
- Capas profissionais em formato portrait (proporção 2:3) adicionadas

**Resultado:**  
✅ eBooks disponíveis na loja com entrega digital automática após pagamento

---

### 4. Configuração do Pagamento — Mercado Pago

**Problema encontrado:**  
Stripe e PayPal estavam listados como opções padrão mas sem configuração. Nenhum gateway de pagamento estava ativo.

**O que foi feito:**  
- Plugin oficial do **Mercado Pago** instalado e ativado
- Conta Mercado Pago vinculada à loja (`efata_sp@yahoo.com.br`)
- Meios de pagamento configurados e ativados:
  - ✅ Checkout Transparente (cartão de crédito/débito)
  - ✅ Checkout Transparente PIX
- Meios desnecessários desativados:
  - ❌ Checkout Pro (redirecionava para fora do site)
  - ❌ Checkout Pro Linha de Crédito
  - ❌ Checkout Transparente Boleto

**Resultado:**  
✅ PIX e cartão de crédito funcionando no checkout  
✅ QR Code PIX gerado automaticamente após finalização do pedido

---

### 5. Identificação e Resolução do Problema Principal — CartFlows

**Problema encontrado:**  
Clientes finalizavam o pedido sem que o pagamento fosse processado. O checkout redirecionava para `/step/checkout-2/` em inglês ("Place Order", "Order Updates") sem gerar o QR Code do PIX.

**Causa raiz identificada:**  
O plugin **CartFlows** estava interceptando o checkout padrão do WooCommerce, substituindo-o por um checkout personalizado incompatível com o Mercado Pago.

**O que foi feito:**  
- CartFlows identificado como vilão do problema
- Plugin **desativado** temporariamente
- Checkout nativo do WooCommerce restaurado

**Resultado:**  
✅ Checkout funcionando corretamente em português  
✅ QR Code PIX gerado e pagamento processado com sucesso  
✅ Pedidos registrados em `WooCommerce → Pedidos`  
✅ Primeira venda real confirmada com crédito no banco

---

### 6. Configuração de E-mail — Notificações de Venda

**Problema encontrado:**  
Após pagamento confirmado, nem o lojista nem o cliente recebiam e-mail de confirmação. O servidor da HostGator bloqueava o envio de e-mails do WordPress.

**O que foi feito:**

**Etapa 1 — Criação do e-mail profissional**
- Criado `loja@efatabook.com.br` no cPanel da HostGator
- E-mail redirecionado para `efatabook@gmail.com`

**Etapa 2 — Plugin WP Mail SMTP**
- Plugin **WP Mail SMTP** instalado e configurado
- Tentativas com Outro SMTP (HostGator) falharam por bloqueio do servidor

**Etapa 3 — Integração com Brevo**
- Conta Brevo criada (`app.brevo.com`)
- Chave API gerada e configurada no WP Mail SMTP
- IP do servidor autorizado no Brevo (`50.6.138.149`)

**Etapa 4 — Autenticação do domínio**
- 4 registros DNS adicionados no cPanel da HostGator (Zone Editor):

| Tipo | Nome | Valor |
|---|---|---|
| TXT | efatabook.com.br | `brevo-code:629a86f7d61...` |
| CNAME | brevo1._domainkey | `b1.efatabook-com-br.dkim.brevo.com` |
| CNAME | brevo2._domainkey | `b2.efatabook-com-br.dkim.brevo.com` |
| TXT | _dmarc | `v=DMARC1; p=none; rua=mailto:rua@dmarc.brevo.com` |

- Domínio `efatabook.com.br` autenticado com sucesso no Brevo

**Resultado:**  
✅ E-mail de teste enviado com **Sucesso!**  
✅ Notificações de novo pedido chegando para `loja@efatabook.com.br`  
✅ E-mail de confirmação enviado automaticamente ao cliente após compra

---

## 📊 Resultado Final — Status da Loja

| Item | Status |
|---|---|
| Página da loja | ✅ Funcionando |
| Produtos com imagem visíveis | ✅ Funcionando |
| eBooks com entrega automática | ✅ Funcionando |
| Checkout em português | ✅ Funcionando |
| Pagamento PIX | ✅ Funcionando |
| Pagamento Cartão | ✅ Funcionando |
| Registro de pedidos | ✅ Funcionando |
| E-mail de confirmação ao cliente | ✅ Funcionando |
| E-mail de novo pedido ao lojista | ✅ Funcionando |
| Domínio de e-mail autenticado | ✅ Funcionando |

---

## 🔴 Problemas Encontrados e Soluções Aplicadas

| Problema | Causa | Solução |
|---|---|---|
| Loja com erro 404 | Página não criada | Criada página "Loja" e configurada no WooCommerce |
| Produtos sem imagem na loja | Upload não realizado | Movidos para Rascunho até imagens serem adicionadas |
| Preço incorreto na Bíblia de Púlpito | Erro de digitação | Corrigido de R$ 2,49 para R$ 2.490,00 |
| Pagamento não processado | CartFlows interceptando checkout | CartFlows desativado |
| Checkout em inglês | CartFlows sobrepondo WooCommerce | CartFlows desativado |
| E-mail não enviado | HostGator bloqueando SMTP | Integração com Brevo via WP Mail SMTP |
| Brevo bloqueando envios | Domínio não autenticado | Registros DNS adicionados e domínio autenticado |

---

## 📌 Pendências e Próximos Passos

### 🔴 Alta Prioridade
- [ ] Completar imagens dos produtos em Rascunho (70 produtos)
- [ ] Reorganizar categorias da loja (estrutura correta)
- [ ] Configurar frete pelos Correios

### 🟡 Média Prioridade
- [ ] Melhorar visual da homepage
- [ ] Adicionar filtros de categoria na loja
- [ ] Configurar produtos relacionados
- [ ] Melhorar UX para dispositivos móveis

### 🟢 Baixa Prioridade
- [ ] Configurar CartFlows corretamente para funis de venda
- [ ] Criar página dedicada do Projeto Amor Pela Palavra
- [ ] Blog com conteúdo cristão e SEO
- [ ] Automações de e-mail marketing

---

## 🙏 Observação Final

Este projeto não é apenas uma loja virtual — é uma ferramenta ministerial a serviço do **Projeto Amor Pela Palavra**, que promove a leitura, audição e confiança na Palavra de Deus, incluindo distribuição gratuita de Bíblias e evangelização.

> *"O Senhor dará a palavra; grande será o exército das que levam as boas-novas."*  
> — Salmos 68:11 (ACF)

---

*Documento gerado em Junho de 2026*  
*Repositório: [github.com/efatabook](https://github.com/efatabook)*
