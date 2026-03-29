from flask import Flask, render_template, request, jsonify

app = Flask(__name__)

# ─── Constantes do seu negócio ───────────────────────────────────────────────
DESPESAS_FIXAS_MES = 3633.08      # R$ locação + celular + lava-rápido
FRANQUIA_KM_MES    = 5000         # km contratados por mês
DIAS_TRABALHADOS   = 26           # dias úteis por mês
META_DIARIA        = 250.00       # R$ mínimo no app por dia

CUSTO_KM_LOCACAO   = DESPESAS_FIXAS_MES / FRANQUIA_KM_MES   # R$ 0,69/km
FRANQUIA_KM_DIA    = FRANQUIA_KM_MES / DIAS_TRABALHADOS      # 192 km/dia

# ─── Rota principal ──────────────────────────────────────────────────────────
@app.route('/')
def index():
    return '<h1>Moto-App funcionando!</h1><p>Acesse /calculadora em breve.</p>'

# ─── Rota da calculadora ─────────────────────────────────────────────────────
@app.route('/calculadora', methods=['GET', 'POST'])
def calculadora():
    resultado = None

    if request.method == 'POST':
        distancia     = float(request.form['distancia'])
        retorno_vazio = request.form.get('retorno_vazio') == 'on'
        autonomia     = float(request.form['autonomia'])
        preco_etanol  = float(request.form['preco_etanol'])

        # Se tem retorno vazio, o custo real é sobre a distância dobrada
        km_real = distancia * 2 if retorno_vazio else distancia

        custo_combustivel = (km_real / autonomia) * preco_etanol
        custo_locacao     = km_real * CUSTO_KM_LOCACAO
        custo_total       = custo_combustivel + custo_locacao

        valor_minimo = custo_total
        valor_ideal  = custo_total * 1.4   # 40% acima do custo = lucro

        resultado = {
            'km_real'          : km_real,
            'custo_combustivel': round(custo_combustivel, 2),
            'custo_locacao'    : round(custo_locacao, 2),
            'custo_total'      : round(custo_total, 2),
            'valor_minimo'     : round(valor_minimo, 2),
            'valor_ideal'      : round(valor_ideal, 2),
        }

    return jsonify(resultado) if resultado else '''
    <h2>Calculadora de Corrida</h2>
    <form method="POST">
        Distância (km): <input name="distancia" type="number" step="0.1" required><br><br>
        Retorno vazio? <input name="retorno_vazio" type="checkbox"><br><br>
        Autonomia (km/L): <input name="autonomia" type="number" step="0.1" value="8" required><br><br>
        Preço etanol (R$/L): <input name="preco_etanol" type="number" step="0.01" value="4.29" required><br><br>
        <button type="submit">Calcular</button>
    </form>
    '''

# ─── Inicialização ───────────────────────────────────────────────────────────
if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
