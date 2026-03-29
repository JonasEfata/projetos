Aqui está o relatório completo:

---

## Moto-App — Relatório de Progresso
**Data:** 29/03/2026 | **Projeto:** Controle financeiro para motorista de aplicativo

---

### Ambiente configurado

- **SO:** Linux Ubuntu 24 | **Editor:** VS Code
- **Python:** 3.12.3 | **Flask:** 3.1.3
- **Repositório GitHub:** `~/projetos/` | **Pasta do projeto:** `~/projetos/moto-app/`
- **Ambiente virtual:** `venv` (ativar com `source venv/bin/activate`)
- **Alias de commit:** `up-proj` — script em `~/up-proj.sh` *(pendente: ajustar permissão)*

---

### Arquivos criados

| Arquivo | Descrição |
|---|---|
| `app.py` | Servidor Flask principal |
| `.gitignore` | Exclui `venv/`, `__pycache__/`, `.env`, `instance/` |

---

### Funcionalidades implementadas

**Rota `/`** — Página inicial confirmando que o servidor está no ar.

**Rota `/calculadora`** — Calculadora de corridas com:
- Entrada: distância (km), retorno vazio (checkbox), autonomia (km/L), preço do etanol (R$/L)
- Lógica: se retorno vazio, dobra o km para cálculo do custo real
- Saída: custo combustível, custo locação, custo total, valor mínimo e valor ideal (+40%)

---

### Constantes do negócio já no código

| Constante | Valor |
|---|---|
| Despesas fixas/mês | R$ 3.633,08 |
| Franquia mensal | 5.000 km |
| Dias trabalhados | 26 dias |
| Meta diária mínima | R$ 250,00 |
| Custo locação/km | R$ 0,69/km |
| Franquia diária | 192 km/dia |

---

### Como iniciar o servidor

```bash
cd ~/projetos/moto-app
source venv/bin/activate
python3 app.py
```

Acesso local: `http://127.0.0.1:5000`
Acesso pelo celular (Wi-Fi): `http://192.168.1.14:5000`

---

### Pendências antes da próxima etapa

- [ ] Corrigir permissão do alias `up-proj` (`chmod +x ~/up-proj.sh`)
- [ ] Verificar se o commit foi enviado ao GitHub

---

### Próximas etapas planejadas

| Etapa | Descrição |
|---|---|
| 3 | Banco de dados SQLite — salvar corridas e registros diários |
| 4 | Formulário de registro diário (faturamento + km) |
| 5 | Painel mensal — lucro real, franquia consumida |
| 6 | Alertas — corrida abaixo do mínimo, franquia estourando, dia abaixo da meta |
| 7 | Interface mobile — tela agradável para uso no celular |

---

Salva este relatório em `~/projetos/moto-app/PROGRESSO.md` se quiser ter ele dentro do projeto. Até a próxima sessão! 🚀