codigo = '''from flask import Flask, request
from database import get_connection, criar_tabelas
from datetime import date

app = Flask(__name__)
criar_tabelas()

DESPESAS_FIXAS_MES = 3633.08
FRANQUIA_KM_MES    = 5000
DIAS_TRABALHADOS   = 26
CUSTO_KM_LOCACAO   = DESPESAS_FIXAS_MES / FRANQUIA_KM_MES

@app.route("/")
def index():
    return "<h1>Moto-App!</h1><p><a href=/calculadora>Calculadora</a></p>"

@app.route("/calculadora", methods=["GET", "POST"])
def calculadora():
    if request.method == "POST":
        distancia     = float(request.form["distancia"])
        retorno_vazio = request.form.get("retorno_vazio") == "on"
        autonomia     = float(request.form["autonomia"])
        preco_etanol  = float(request.form["preco_etanol"])
        valor_cobrado = request.form.get("valor_cobrado") or None
        if valor_cobrado:
            valor_cobrado = float(valor_cobrado)
        km_real           = distancia * 2 if retorno_vazio else distancia
        custo_combustivel = (km_real / autonomia) * preco_etanol
        custo_locacao     = km_real * CUSTO_KM_LOCACAO
        custo_total       = custo_combustivel + custo_locacao
        valor_ideal       = custo_total * 1.4
        conn = get_connection()
        conn.execute(
            "INSERT INTO corridas (data, distancia, retorno_vazio, autonomia, "
            "preco_etanol, km_real, custo_total, valor_cobrado) "
            "VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            (date.today().isoformat(), distancia, int(retorno_vazio),
             autonomia, preco_etanol, km_real, round(custo_total, 2), valor_cobrado)
        )
        conn.commit()
        conn.close()
        html  = "<h2>Resultado</h2>"
        html += "<p>Km real: " + str(km_real) + " km</p>"
        html += "<p>Custo combustivel: R$ " + "{:.2f}".format(custo_combustivel) + "</p>"
        html += "<p>Custo locacao: R$ " + "{:.2f}".format(custo_locacao) + "</p>"
        html += "<p><strong>Custo total: R$ " + "{:.2f}".format(custo_total) + "</strong></p>"
        html += "<p>Valor ideal (+40%): R$ " + "{:.2f}".format(valor_ideal) + "</p>"
        html += "<p><a href=/calculadora>Nova corrida</a> | <a href=/corridas>Ver corridas</a></p>"
        return html
    return (
        "<h2>Calculadora de Corrida</h2>"
        "<form method=POST>"
        "Distancia (km): <input name=distancia type=number step=0.1 required><br><br>"
        "Retorno vazio? <input name=retorno_vazio type=checkbox><br><br>"
        "Autonomia (km/L): <input name=autonomia type=number step=0.1 value=8 required><br><br>"
        "Preco etanol: <input name=preco_etanol type=number step=0.01 value=4.29 required><br><br>"
        "Valor cobrado: <input name=valor_cobrado type=number step=0.01><br><br>"
        "<button type=submit>Calcular</button></form>"
    )

@app.route("/corridas")
def corridas():
    conn = get_connection()
    rows = conn.execute("SELECT * FROM corridas ORDER BY criado_em DESC").fetchall()
    conn.close()
    html  = "<h2>Corridas Registradas</h2>"
    html += "<table border=1 cellpadding=8>"
    html += "<tr><th>Data</th><th>Distancia</th><th>Custo</th><th>Cobrado</th></tr>"
    for r in rows:
        cobrado = "R$ " + "{:.2f}".format(r["valor_cobrado"]) if r["valor_cobrado"] else "-"
        html += "<tr><td>" + r["data"] + "</td>"
        html += "<td>" + str(r["distancia"]) + " km</td>"
        html += "<td>R$ " + "{:.2f}".format(r["custo_total"]) + "</td>"
        html += "<td>" + cobrado + "</td></tr>"
    html += "</table><p><a href=/calculadora>Nova corrida</a></p>"
    return html

if __name__ == "__main__":
    app.run(debug=True, host="0.0.0.0", port=5000)
'''

with open("app.py", "w") as f:
    f.write(codigo)
print("app.py criado com sucesso!")