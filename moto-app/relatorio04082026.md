Moto-App — Relatório de Progresso

Data: 04/08/2026 | Sessão: Etapa 4 — Estrutura do banco expandida

O que foi feito nesta sessão

Leitura do código — revisão dos conceitos da sessão anterior: importações, variáveis, constantes, rotas, GET vs POST.

Etapa 4 iniciada — planejamento e criação das novas tabelas do banco de dados.

Duas novas tabelas criadas no database.py:

Tabela	Função
dias	Cabeçalho do dia — data, km inicial, km final
lancamentos	Entradas e saídas — tipo, categoria, descrição, valor

Conceito aprendido — chave estrangeira:
O campo dia_id na tabela lancamentos liga cada lançamento ao seu dia na tabela dias. Isso se chama relacionamento entre tabelas.