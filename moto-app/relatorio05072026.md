Aqui está o relatório completo desta sessão:

---

## Moto-App — Relatório de Progresso
**Data:** 05/07/2026 | **Sessão:** Retomada após 3 meses

---

### Situação ao retomar

- Projeto pausado desde 29/03/2026
- VS Code havia sido removido e precisou ser reinstalado
- Ambiente virtual (`venv`) estava corrompido e precisou ser recriado
- `app.py` precisou ser reescrito por problemas de copy/paste

---

### Problemas resolvidos nesta sessão

| Problema | Solução |
|---|---|
| VS Code removido | Reinstalado via repositório Microsoft |
| venv corrompido | Apagado e recriado com `python3 -m venv venv` |
| Flask não encontrado | Reinstalado dentro do venv correto |
| `app.py` com erros de sintaxe | Recriado via script `make_app.py` |
| Alias `up-proj` sem permissão | Corrigido com `chmod 755` e `.bashrc` limpo |

---

### Estado atual do projeto

**Pasta:** `~/projetos/moto-app/`

| Arquivo | Função |
|---|---|
| `app.py` | Servidor Flask principal |
| `database.py` | Conexão e criação das tabelas SQLite |
| `moto.db` | Banco de dados com corridas salvas |
| `make_app.py` | Script auxiliar que gerou o app.py |
| `.gitignore` | Exclui venv e arquivos temporários |

---

### Funcionalidades funcionando

**Rota `/`** — Página inicial

**Rota `/calculadora`** — Calcula custo real da corrida com:
- Distância, retorno vazio, autonomia, preço do etanol
- Custo combustível + custo locação + custo total
- Valor ideal (+40%)
- Campo opcional para valor cobrado
- Salva cada corrida no banco automaticamente

**Rota `/corridas`** — Lista todas as corridas registradas com data, distância, custo e valor cobrado

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

### Próximas etapas

| Etapa | Descrição |
|---|---|
| **4** | Registro diário — formulário para lançar faturamento e km do dia |
| **5** | Painel mensal — lucro real, franquia consumida, dias abaixo da meta |
| **6** | Alertas — corrida abaixo do mínimo, franquia estourando |
| **7** | Interface mobile — tela agradável para uso no celular |

---

Bom descanso, Jonas! O projeto está sólido e avançando bem. 🙏🚀