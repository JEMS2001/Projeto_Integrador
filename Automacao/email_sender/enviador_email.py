from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.application import MIMEApplication
from email.mime.image import MIMEImage
import smtplib
import logging

class EnviadorEmail:
    def __init__(self, smtp_server, porta, remetente, senha):
        self.smtp_server = smtp_server
        self.porta = porta
        self.remetente = remetente
        self.senha = senha
        self.logger = logging.getLogger(__name__)

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
            try:
                parte_anexo = MIMEApplication(anexo.getvalue())
                parte_anexo.add_header('Content-Disposition', 'attachment', filename='relatorio.xlsx')
                msg.attach(parte_anexo)
            except Exception as e:
                self.logger.error(f"Erro ao anexar o arquivo: {str(e)}")
                return False
        
        if imagens:
            for nome_imagem, conteudo_imagem in imagens:
                try:
                    imagem = MIMEImage(conteudo_imagem)
                    imagem.add_header('Content-ID', f'<{nome_imagem}>')
                    msg.attach(imagem)
                except Exception as e:
                    self.logger.error(f"Erro ao anexar a imagem {nome_imagem}: {str(e)}")
                    return False
        
        try:
            with smtplib.SMTP(self.smtp_server, self.porta) as server:
                server.starttls()
                server.login(self.remetente, self.senha)
                server.send_message(msg)
            self.logger.info(f"E-mail enviado com sucesso para {destinatario}")
            return True
        except smtplib.SMTPAuthenticationError:
            self.logger.error("Erro de autenticação SMTP. Verifique as credenciais.")
            return False
        except smtplib.SMTPException as e:
            self.logger.error(f"Erro SMTP ao enviar o e-mail: {str(e)}")
            return False
        except Exception as e:
            self.logger.error(f"Erro inesperado ao enviar o e-mail: {str(e)}")
            return False

    def validar_configuracoes(self):
        try:
            with smtplib.SMTP(self.smtp_server, self.porta) as server:
                server.starttls()
                server.login(self.remetente, self.senha)
            self.logger.info("Configurações de e-mail validadas com sucesso.")
            return True
        except Exception as e:
            self.logger.error(f"Erro ao validar as configurações de e-mail: {str(e)}")
            return False