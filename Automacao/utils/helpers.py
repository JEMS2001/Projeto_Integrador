def formatar_data(data):
    return data.strftime('%d/%m/%Y')

def calcular_diferenca_dias(data_inicio, data_fim):
    return (data_fim - data_inicio).days