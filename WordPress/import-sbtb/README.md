
Protege o trabalho de marketing e UX do lojista.

**Fluxo:**
1. **Primeira importação** → produto entra com o nome técnico da planilha (ex: `Bíblia ACF de Púlpito - Vinho - cód. 9001`)
2. **Você edita** o produto no WooCommerce e coloca o nome comercial (ex: `Bíblia Sagrada de Púlpito - Edição Luxo Vinho`)
3. **Próximas importações** → o plugin reconhece o produto pelo EAN e atualiza só preço/estoque — o nome comercial fica intacto

---

## Instalação

1. Copie a pasta `efatabook-importer/` para `wp-content/plugins/`
2. Ative em **Painel → Plugins → Efatabook Importer**
3. WooCommerce deve estar instalado e ativo

---

## Como usar

### Importar planilha
**WooCommerce → Importar Planilha ACF**
1. Selecione o arquivo `.csv` exportado da SBTB (sem editar nada)
2. Clique em **Importar Agora**
3. Veja o relatório: quantos produtos foram criados, atualizados ou tiveram erro

### Ver e regenerar SKUs
**WooCommerce → SKUs Efatabook**
- Tabela completa com: nome comercial, nome do fornecedor, EAN, SKU atual
- Botão **Regenerar todos os SKUs** para corrigir SKUs caso necessário

### Campos por produto
Na tela de edição de cada produto (aba Geral):

| Campo | Descrição |
|---|---|
| EAN | Código de barras — identificador único |
| Nome Original (Fornecedor) | Nome técnico da planilha — referência |
| Código Interno | Código do fornecedor (ex: 6801) |
| Fornecedor | Sigla (ex: ACF, Efatabook) |

---

## Estrutura do projeto

```
efatabook-importer/
└── efatabook-importer.php   # Plugin completo (único arquivo)
```

---

## Requisitos

- WordPress 6.0+
- WooCommerce 7.0+
- PHP 8.0+

---

## Versionamento

Mantido via **VS Code + GitHub**.
- `main` → versão estável em produção
- Crie uma branch para cada nova funcionalidade
- Abra Pull Request para revisão antes de fazer merge

---

## Changelog

### 2.0.0
- Reescrita completa com os três pilares
- Importação direta pelo painel (sem CSV intermediário)
- SKU baseado em prefixo do EAN + ID do WordPress
- Máscara Comercial: nome nunca sobrescrito após primeira importação
- Relatório detalhado pós-importação
- Página de gestão de SKUs

### 1.0.0
- Versão inicial com geração manual de SKU por fornecedor
