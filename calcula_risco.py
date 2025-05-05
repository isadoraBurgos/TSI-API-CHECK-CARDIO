import http.server
import socketserver
import urllib.parse

def calcular_risco(idade, sexo, dor_peito, pressao, colesterol, glicemia, eletro, freq_cardiaca, angina, depressao_st, inclinacao_st, vasos, talassemia):
    pontos = 0
    if idade >= 60:
        pontos += 3
    elif idade >= 40:
        pontos += 2
    else:
        pontos += 1
    if sexo == 1 and idade < 55:
        pontos += 2
    elif sexo == 0 and idade >= 55:
        pontos += 2
    if dor_peito == 0:
        pontos += 3
    elif dor_peito == 1:
        pontos += 2
    elif dor_peito == 2:
        pontos += 1
    if pressao >= 160:
        pontos += 3
    elif pressao >= 140:
        pontos += 2
    elif pressao >= 120:
        pontos += 1
    if colesterol >= 240:
        pontos += 3
    elif colesterol >= 200:
        pontos += 2
    else:
        pontos += 1
    if glicemia == 1:
        pontos += 2
    if eletro == 2:
        pontos += 3
    elif eletro == 1:
        pontos += 2
    if freq_cardiaca < 100:
        pontos += 2
    elif freq_cardiaca > 180:
        pontos += 1
    if angina == 1:
        pontos += 3
    if depressao_st >= 2:
        pontos += 3
    elif depressao_st > 0:
        pontos += 2
    if inclinacao_st == 2:
        pontos += 3
    elif inclinacao_st == 1:
        pontos += 2
    pontos += vasos * 2
    if talassemia in [1, 2]:
        pontos += 3
    elif talassemia == 3:
        pontos += 2
    if pontos >= 30:
        return "Alto risco"
    elif pontos >= 20:
        return "Risco moderado"
    else:
        return "Baixo risco"

class MyHandler(http.server.SimpleHTTPRequestHandler):
    def do_GET(self):
        query = urllib.parse.urlparse(self.path).query
        params = urllib.parse.parse_qs(query)
        try:
            idade = int(params.get('idade', [0])[0])
            sexo = int(params.get('sexo', [0])[0])
            dor_peito = int(params.get('dor_peito', [0])[0])
            pressao = int(params.get('pressao', [0])[0])
            colesterol = int(params.get('colesterol', [0])[0])
            glicemia = int(params.get('glicemia', [0])[0])
            eletro = int(params.get('eletro', [0])[0])
            freq_cardiaca = int(params.get('freq_cardiaca', [0])[0])
            angina = int(params.get('angina', [0])[0])
            depressao_st = float(params.get('depressao_st', [0])[0])
            inclinacao_st = int(params.get('inclinacao_st', [0])[0])
            vasos = int(params.get('vasos', [0])[0])
            talassemia = int(params.get('talassemia', [0])[0])
        except (ValueError, IndexError):
            self.send_response(400)
            self.send_header('Content-type', 'text/plain')
            self.end_headers()
            self.wfile.write(b'Erro: Parametros invalidos')
            return
        risco = calcular_risco(idade, sexo, dor_peito, pressao, colesterol, glicemia, eletro, freq_cardiaca, angina, depressao_st, inclinacao_st, vasos, talassemia)
        self.send_response(200)
        self.send_header('Content-type', 'text/plain')
        self.end_headers()
        self.wfile.write(risco.encode('utf-8'))

PORT = 8000
with socketserver.TCPServer(("", PORT), MyHandler) as httpd:
    print(f"Servidor rodando na porta {PORT}")
    httpd.serve_forever()