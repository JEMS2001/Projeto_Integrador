
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.application import MIMEApplication
from email.mime.image import MIMEImage
import smtplib

class EnviadorEmail:
    def __init__(self, smtp_server, porta, remetente, senha):
        self.smtp_server = smtp_server
        self.porta = porta
        self.remetente = remetente
        self.senha = senha

    def enviar_email(self, destinatario, assunto, corpo, anexo=None, imagens=None):
        msg = MIMEMultipart('related')
        msg['From'] = self.remetente
        msg['To'] = destinatario
        msg['Subject'] = assunto
        
        msg_alternativa = MIMEMultipart('alternative')
        msg.attach(msg_alternativa)
        
        parte_html = MIMEText(corpo, 'html')
        msg_alternativa.attach(parte_html)
        
        if anexo:
            parte_anexo = MIMEApplication(anexo.getvalue())
            parte_anexo.add_header('Content-Disposition', 'attachment', filename='relatorio.xlsx')
            msg.attach(parte_anexo)
        
        if imagens:
            for nome_imagem, conteudo_imagem in imagens:
                imagem = MIMEImage(conteudo_imagem)
                imagem.add_header('Content-ID', f'<{nome_imagem}>')
                msg.attach(imagem)
        
        try:
            with smtplib.SMTP(self.smtp_server, self.porta) as server:
                server.starttls()
                server.login(self.remetente, self.senha)
                server.send_message(msg)
            print(f"E-mail enviado com sucesso para {destinatario}")
        except Exception as e:
            print(f"Erro ao enviar o e-mail: {e}")