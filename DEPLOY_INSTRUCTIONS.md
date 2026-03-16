# 🚀 Guia de Deploy - Assados Delivery

**Projeto:** Assados Delivery v1.1.0  
**Desenvolvedor:** Luã Bolivar Pedroso  
**Curso:** TADS

---

## 📋 Índice

1. [Pré-requisitos](#pré-requisitos)
2. [Escolher Hospedagem](#escolher-hospedagem)
3. [Preparar Arquivos](#preparar-arquivos)
4. [Upload dos Arquivos](#upload-dos-arquivos)
5. [Configurar Banco de Dados](#configurar-banco-de-dados)
6. [Testar o Site](#testar-o-site)
7. [Troubleshooting](#troubleshooting)

---

## 1️⃣ Pré-requisitos

Antes de começar, você precisa ter:

- ✅ Todos os arquivos do projeto (você já tem!)
- ✅ Uma conta em uma hospedagem gratuita ou paga
- ✅ Cliente FTP (FileZilla) instalado no computador
- ✅ Acesso ao arquivo SQL (`database/schema.sql`)

**Tempo estimado:** 30-45 minutos

---

## 2️⃣ Escolher Hospedagem

### 🌟 Opção 1: InfinityFree (RECOMENDADO - 100% Grátis)

**Por que escolher:**
- ✅ PHP 8.x + MySQL grátis
- ✅ Sem anúncios
- ✅ SSL grátis (HTTPS)
- ✅ Painel cPanel fácil
- ✅ 5GB de espaço

**Como criar conta:**

1. Acesse: https://infinityfree.net
2. Clique em "Sign Up"
3. Preencha: Nome, Email, Senha
4. **Escolha um subdomínio:** 
   - Ex: `assadosdelivery.rf.gd`
   - Ex: `assados.wuaze.com`
   - Ex: `delivery.kesug.com`
5. Anote as credenciais que aparecerem:
   - **FTP Hostname:** (ex: ftpupload.net)
   - **FTP Username:** (ex: epiz_12345678)
   - **FTP Password:** (sua senha)
   - **MySQL Hostname:** (ex: sql123.infinityfree.net)
   - **MySQL Database:** (ex: epiz_12345678_assados)
   - **MySQL Username:** (ex: epiz_12345678)
   - **MySQL Password:** (vai criar na próxima etapa)

---

### 🌟 Opção 2: 000webhost (Alternativa Grátis)

**Como criar conta:**

1. Acesse: https://www.000webhost.com
2. Clique em "Free Sign Up"
3. Escolha: "Build a Website"
4. Preencha dados e escolha subdomínio
5. Anote credenciais de FTP e MySQL

---

## 3️⃣ Preparar Arquivos

### 📝 Passo a Passo:

**1. Renomear arquivo de configuração do banco:**

```
Antes: config/database.production.php
Depois: config/database.php (SOBRESCREVER o arquivo atual)
```

**2. Editar `config/database.php` com as credenciais da hospedagem:**

Abra o arquivo e substitua:

```php
define('DB_HOST', 'seu_host_mysql_aqui');      // Ex: sql123.infinityfree.net
define('DB_NAME', 'seu_banco_aqui');           // Ex: epiz_12345678_assados
define('DB_USER', 'seu_usuario_aqui');         // Ex: epiz_12345678
define('DB_PASS', 'sua_senha_aqui');           // A senha do MySQL
```

**3. Verificar arquivo `.htaccess`:**

O arquivo `.htaccess` já está pronto! Apenas verifique se a linha:

```apache
RewriteBase /
```

Está correta. Se o site estiver em uma subpasta, ajuste para:

```apache
RewriteBase /nome-da-subpasta/
```

**4. Desabilitar exibição de erros (PRODUÇÃO):**

No arquivo `config/config.php`, adicione no topo:

```php
// Desabilitar erros em produção
error_reporting(0);
ini_set('display_errors', 0);
```

---

## 4️⃣ Upload dos Arquivos

### 🔧 Usando FileZilla (Recomendado):

**1. Baixar e instalar FileZilla:**
- Download: https://filezilla-project.org/download.php?type=client
- Instale normalmente

**2. Conectar ao servidor:**

No FileZilla, preencha no topo:
- **Host:** `ftpupload.net` (ou o fornecido pela hospedagem)
- **Usuário:** `epiz_12345678` (ou seu usuário FTP)
- **Senha:** Sua senha FTP
- **Porta:** `21`

Clique em "Quickconnect"

**3. Fazer upload:**

- **Lado esquerdo:** Navegue até a pasta do seu projeto no computador
- **Lado direito:** Vá para a pasta `htdocs` (InfinityFree) ou `public_html` (000webhost)
- **Selecione TODOS os arquivos** do lado esquerdo (exceto `.git` se aparecer)
- **Arraste** para o lado direito
- **Aguarde** o upload terminar (pode levar 5-10 minutos)

**✅ Arquivos que DEVEM ser enviados:**
```
.htaccess
index.php
login.php
carrinho.php
checkout.php
contato.php
sobre.php
minha-conta.php
pedido-confirmado.php
criar_admin.php
admin/ (pasta completa)
api/ (pasta completa)
config/ (pasta completa - COM database.php atualizado!)
controllers/ (pasta completa)
models/ (pasta completa)
views/ (pasta completa)
public/ (pasta completa)
database/ (pasta completa - apenas os .sql)
```

**❌ NÃO envie:**
- `.git/` (pasta Git)
- `.vscode/` (configurações VS Code)
- `*.zip` (arquivos compactados)
- `README.md` (opcional - não atrapalha se enviar)

---

## 5️⃣ Configurar Banco de Dados

### 🗄️ Criar e popular o banco:

**1. Acessar phpMyAdmin:**

- InfinityFree: No painel de controle, clique em "MySQL Databases" → "phpMyAdmin"
- 000webhost: Clique em "Manage Database" → "phpMyAdmin"

**2. Fazer login:**
- Usuário e senha do MySQL (anotados anteriormente)

**3. Importar o SQL:**

- No menu lateral esquerdo, clique no **nome do seu banco** (ex: `epiz_12345678_assados`)
- Clique na aba **"Import"** (Importar)
- Clique em **"Choose File"** (Escolher arquivo)
- Selecione: `database/schema.sql` do seu computador
- Role até o final e clique em **"Go"** (Executar)
- **Aguarde** a mensagem de sucesso ✅

**4. Verificar se funcionou:**

- Clique no nome do banco no menu lateral
- Você deve ver as tabelas:
  - ✅ categorias
  - ✅ produtos
  - ✅ clientes
  - ✅ pedidos
  - ✅ pedidos_itens
  - ✅ usuarios_admin
  - ✅ auditoria_precos

**5. (OPCIONAL) Importar dados de exemplo:**

Se quiser ter produtos de exemplo:
- Repita o processo de importação com `database/seed.sql`

---

## 6️⃣ Testar o Site

### ✅ Checklist de Testes:

**1. Acessar a página inicial:**
```
http://seu-subdominio.rf.gd/
```

Deve aparecer:
- ✅ Header com logo
- ✅ Produtos listados
- ✅ Footer com informações

**2. Testar navegação:**
- ✅ Clique em "Cardápio" → Deve listar produtos
- ✅ Clique em "Contato" → Deve aparecer mapa e telefone
- ✅ Clique em "Sobre" → Deve carregar a página

**3. Testar cadastro de cliente:**
- ✅ Vá em "Login" (topo direito)
- ✅ Preencha formulário de cadastro
- ✅ Clique em "Criar Conta"
- ✅ Deve redirecionar para "Minha Conta"

**4. Testar carrinho:**
- ✅ Adicione um produto ao carrinho
- ✅ Vá em "Carrinho"
- ✅ Deve aparecer o produto
- ✅ Clique em "Finalizar Pedido"

**5. Testar painel admin:**
```
http://seu-subdominio.rf.gd/admin/
```

**PRIMEIRO ACESSO:** Você precisa criar um usuário admin!

Execute no phpMyAdmin:

```sql
INSERT INTO usuarios_admin (nome, email, senha, ativo)
VALUES ('Admin', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
```

**Login padrão:**
- Email: `admin@admin.com`
- Senha: `password`

**⚠️ IMPORTANTE:** Mude a senha depois de logar!

---

## 7️⃣ Troubleshooting (Resolução de Problemas)

### 🔴 Erro: "Erro ao conectar com o banco de dados"

**Solução:**
1. Verifique se as credenciais em `config/database.php` estão corretas
2. Certifique-se de que o banco foi criado no painel da hospedagem
3. Teste a conexão no phpMyAdmin primeiro

---

### 🔴 Erro: "Internal Server Error" ou "500"

**Solução:**
1. Verifique o arquivo `.htaccess` - pode ter alguma diretiva incompatível
2. Teste comentando as linhas do `.htaccess` uma por uma
3. Verifique logs de erro no painel da hospedagem

---

### 🔴 Erro: "404 Not Found" em páginas

**Solução:**
1. Verifique se o arquivo `.htaccess` foi enviado
2. Certifique-se de que `mod_rewrite` está habilitado (geralmente já está)
3. Ajuste `RewriteBase` no `.htaccess`

---

### 🔴 Imagens não aparecem

**Solução:**
1. Verifique se a pasta `public/assets/img/` foi enviada
2. Confirme permissões da pasta (755 ou 777)
3. Teste o caminho direto: `http://seu-site.com/public/assets/img/logo.png`

---

### 🔴 Upload de imagens não funciona

**Solução:**
1. Verifique permissões da pasta `public/assets/img/produtos/` (deve ser 777)
2. No painel FTP, clique com botão direito na pasta → Properties → Permissions → 777
3. Aumente limites no `.htaccess` (já está configurado)

---

## 🎉 Pronto! Site no Ar!

Após concluir todos os passos, seu site estará online e acessível em:

```
http://seu-subdominio.rf.gd/
```

### 📊 Próximos Passos:

1. ✅ Adicione produtos reais
2. ✅ Personalize cores e textos
3. ✅ Configure SSL (HTTPS) no painel da hospedagem
4. ✅ Teste todas as funcionalidades
5. ✅ Compartilhe o link!

---

## 📞 Suporte

Se tiver problemas:
1. Verifique os logs de erro no painel da hospedagem
2. Consulte a documentação da hospedagem
3. Revise este guia passo a passo

---

**Desenvolvido por:** Luã Bolivar Pedroso  
**Projeto Acadêmico:** TADS - Novembro 2025  
**Versão:** 1.1.0

**Boa sorte! 🚀**
