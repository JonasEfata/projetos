## Moto-App — Relatório de Progresso
**Data:** 07/08/2026 | **Sessão:** Interface visual e menu principal

---

### O que foi feito nesta sessão

**Interface visual criada** — o projeto ganhou cara de aplicativo usando Bootstrap, uma biblioteca de estilos gratuita que funciona bem no celular.

**Dois arquivos HTML criados na pasta `templates`:**

| Arquivo | Função |
|---|---|
| `base.html` | Esqueleto visual de todas as páginas — navbar, Bootstrap, estrutura |
| `index.html` | Menu principal com 4 botões de navegação |

**`app.py` atualizado:**
- `render_template` adicionado na importação
- Rota `/` atualizada para carregar o menu visual com a data de hoje

---

### Conceitos aprendidos hoje

- **Bootstrap** — biblioteca de estilos que deixa o visual bonito sem escrever CSS do zero
- **Templates HTML** — arquivos separados do Python que definem o visual
- **`render_template`** — função do Flask que conecta o Python ao HTML
- **`{% block conteudo %}`** — como o Flask "encaixa" cada página dentro do esqueleto base
- **`mv`** — comando para mover arquivos no terminal
