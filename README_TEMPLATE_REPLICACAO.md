# Template de Replicacao de Projeto Web (PHP + MySQL)

Use este documento como base para replicar um sistema para uma nova empresa.

Preencha os campos entre colchetes antes da entrega final.

---

## 1. Dados do Projeto

- Nome da empresa: [NOME_DA_EMPRESA]
- Nome do sistema: [NOME_DO_SISTEMA]
- Dominio/URL: [URL_PUBLICA]
- Responsavel tecnico: [NOME_RESPONSAVEL]
- Data da implantacao: [AAAA-MM-DD]

---

## 2. Stack Tecnica

- Backend: PHP [VERSAO]
- Banco: MySQL/MariaDB [VERSAO]
- Frontend: HTML/CSS/JavaScript
- Ambiente local: XAMPP/WAMP/LAMP

---

## 3. Setup Local Rapido

1. Copiar projeto para pasta web local.
2. Configurar banco no arquivo de conexao.
3. Importar schema e seed.
4. Subir servidor web e banco.
5. Acessar URL local e validar telas principais.

Campos de conexao:
- DB_HOST = [HOST]
- DB_NAME = [BANCO]
- DB_USER = [USUARIO]
- DB_PASS = [SENHA]

---

## 4. Personalizacao Obrigatoria

### 4.1 Marca

- Nome no site
- Slogan
- Logo/favicon
- Paleta de cores
- Tipografia

### 4.2 Contatos

- WhatsApp: [DDD + NUMERO]
- Instagram: [URL_INSTAGRAM]
- Cidade/UF: [CIDADE_UF]

### 4.3 Conteudo

- Pagina Sobre
- Pagina Contato
- Politicas (se aplicavel)
- Rodape institucional

---

## 5. Banco de Dados

### 5.1 Ordem de execucao sugerida

1. schema principal
2. migracoes complementares
3. seed inicial

### 5.2 Usuario administrativo inicial

- Email: [ADMIN_EMAIL]
- Senha inicial: [ADMIN_SENHA_TEMPORARIA]
- Acao obrigatoria: trocar senha no primeiro acesso.

---

## 6. Checklist de Homologacao

1. Login admin funcionando.
2. CRUD principal funcionando.
3. Upload de imagem funcionando.
4. Links de WhatsApp corretos.
5. Links de Instagram corretos.
6. Responsividade mobile ok.
7. Sem erros PHP no log.
8. Dados sensiveis revisados.

---

## 7. Checklist de Producao

1. display_errors desligado.
2. Senhas padrao removidas.
3. Backup inicial do banco realizado.
4. HTTPS ativo.
5. Permissoes de pasta revisadas.
6. Monitoramento/rotina de backup definida.

---

## 8. Handoff ao Cliente

Entregar ao cliente:
- URL do sistema
- URL do admin
- usuario admin e instrucoes de troca de senha
- mini manual operacional (cadastro, venda, atendimento)
- contato de suporte tecnico

---

## 9. Observacoes da Implantacao

- [REGISTRAR AJUSTES ESPECIFICOS DESTE CLIENTE]
- [REGISTRAR LIMITACOES/DECISOES TECNICAS]
- [REGISTRAR PENDENCIAS POS-GO-LIVE]
