# Bora Desapegar - Guia de Implantacao e Replicacao

Este repositorio contem um sistema web em PHP (arquitetura MVC) para catalogo e venda de pecas de brecho infantil.

Objetivo deste README:
- explicar como rodar o projeto localmente;
- documentar como replicar o mesmo sistema para outra empresa;
- servir como checklist de configuracao e entrega.

---

## 1. Visao Geral

O sistema possui duas areas principais:
- Area publica: listagem de pecas, carrinho, checkout, paginas institucionais.
- Area administrativa: dashboard, gestao de pecas, vendas, clientes, pedidos e mensagens.

Principais tecnologias:
- PHP 8+
- MySQL/MariaDB
- PDO (prepared statements)
- HTML/CSS/JavaScript (sem framework)

---

## 2. Estrutura do Projeto

```text
desapega/
|- admin/                # Painel administrativo
|- api/                  # Endpoints auxiliares
|- config/               # Configuracoes (site e banco)
|- controllers/          # Regras de negocio
|- models/               # Acesso a dados
|- views/partials/       # Header e footer compartilhados
|- public/assets/        # CSS, JS e imagens
|- database/             # Scripts SQL de schema e seed
|- index.php             # Home
|- cardapio.php          # Catalogo
|- checkout.php          # Finalizacao de pedido
|- contato.php           # Pagina de contato
|- sobre.php             # Pagina institucional
```

Arquivos mais importantes para configuracao:
- `config/database.php`
- `config/config.php`
- `database/schema_bora_desapegar.sql`
- `database/seed_bora_desapegar.sql`

---

## 3. Como Rodar Localmente (XAMPP)

### 3.1 Pre-requisitos
- XAMPP (Apache + MySQL)
- PHP 8 ou superior
- Navegador web

### 3.2 Passo a passo

1. Copie o projeto para:
   - `C:/xampp/htdocs/desapega`

2. Inicie Apache e MySQL no XAMPP.

3. Crie/importe o banco com os scripts do projeto.

Opcao via terminal (Windows):

```bash
C:\xampp\mysql\bin\mysql -u root -e "SOURCE C:/xampp/htdocs/desapega/database/schema_bora_desapegar.sql"
C:\xampp\mysql\bin\mysql -u root -e "SOURCE C:/xampp/htdocs/desapega/database/seed_bora_desapegar.sql"
```

4. Confirme as credenciais em `config/database.php`:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'bora_desapegar');
define('DB_USER', 'root');
define('DB_PASS', '');
```

5. Acesse no navegador:
   - `http://localhost/desapega`

### 3.3 Acesso administrativo padrao
- URL: `http://localhost/desapega/admin/`
- Email: `admin@boradesapegar.com`
- Senha: `admin123`

---

## 4. Banco de Dados (Resumo)

Schema principal de replicacao: `database/schema_bora_desapegar.sql`

Tabelas centrais:
- `usuarios_admin`
- `pecas`
- `vendas`

Seed recomendado para ambiente novo:
- `database/seed_bora_desapegar.sql`

Observacao:
- Existem scripts legados do projeto antigo no diretorio `database/`.
- Para replicar o modelo atual de brecho, utilize sempre os arquivos com sufixo `bora_desapegar`.

---

## 5. Guia de Replicacao Para Outra Empresa

Use esta sequencia para clonar o sistema para uma nova marca.

### 5.1 Duplicar o projeto

1. Copie a pasta `desapega` para um novo nome (exemplo: `empresa-x`).
2. Ajuste o caminho no Apache/XAMPP conforme a nova pasta.

### 5.2 Criar banco da nova empresa

1. Duplique o schema atual e troque o nome do banco.
2. Exemplo: `empresa_x_brecho`.
3. Atualize `config/database.php` com o novo nome de banco.

### 5.3 Atualizar identidade da marca

Edite `config/config.php`:
- `SITE_NAME`
- `SITE_SLOGAN`
- `SITE_URL`
- `SITE_TELEFONE`
- `SITE_INSTAGRAM`
- `SITE_CIDADE`
- `SITE_ESTADO`

### 5.4 Atualizar contatos e links diretos

Mesmo com constantes, alguns links podem estar fixos nas paginas.
Revise principalmente:
- `views/partials/header.php`
- `views/partials/footer.php`
- `contato.php`
- `index.php`
- `cardapio.php`
- `pedido-confirmado.php`

Trocas tipicas:
- Numero do WhatsApp (`wa.me/55...`)
- @ do Instagram
- Textos de atendimento e cidade

### 5.5 Personalizar visual

1. Ajuste cores e componentes em `public/assets/css/style.css`.
2. Troque imagens e placeholders em `public/assets/img/`.
3. Revise textos institucionais nas paginas `sobre.php` e `contato.php`.

### 5.6 Revisar dados iniciais

1. Atualize o seed com categorias/pecas reais da nova empresa.
2. Crie usuario admin definitivo e altere a senha padrao.

### 5.7 Matriz rapida de personalizacao

Use esta matriz para acelerar a replicacao:

| Item | Onde alterar | Exemplo |
|---|---|---|
| Nome da empresa | `config/config.php` -> `SITE_NAME` | Empresa X Kids |
| Slogan | `config/config.php` -> `SITE_SLOGAN` | Moda infantil seminova com economia |
| URL base | `config/config.php` -> `SITE_URL` | http://localhost/empresa-x |
| Telefone | `config/config.php` + links fixos | (11) 99999-9999 |
| Instagram | `config/config.php` + header/footer/contato | https://instagram.com/empresa_x |
| Cidade/UF | `config/config.php` | Sao Paulo / SP |
| Cores e identidade | `public/assets/css/style.css` | nova paleta da marca |
| Conteudo institucional | `sobre.php` e `contato.php` | texto da nova empresa |
| Credenciais admin | banco `usuarios_admin` | usuario e senha exclusivos |

---

## 6. Fluxo Operacional Recomendado

1. Admin cadastra pecas em `admin/pecas.php`.
2. Cliente navega no catalogo (`cardapio.php`) e monta carrinho.
3. Checkout registra pedido.
4. Admin acompanha pedidos/vendas no painel.

---

## 7. Checklist de Go-live

Antes de publicar para cliente final:

1. Banco importado sem erro (schema + seed).
2. Login admin funcionando.
3. Cadastro de peca com upload de imagem funcionando.
4. Catalogo exibindo imagens corretamente.
5. Botao de WhatsApp abrindo o numero correto.
6. Link de Instagram correto no topo/rodape/contato.
7. Texto de marca atualizado em todas as paginas.
8. Senha admin padrao alterada.
9. `display_errors` desativado em producao.

---

## 8. Troubleshooting Rapido

Erro de conexao com banco:
- verifique host, nome do banco, usuario e senha em `config/database.php`.

Pagina sem estilo atualizado:
- limpe cache do navegador;
- se necessario, aplique versao no CSS (cache busting).

Upload de imagem falhando:
- verifique permissao de escrita em `public/assets/img/pecas/`;
- confirme extensao GD habilitada no PHP.

WhatsApp abrindo numero antigo:
- procure por `wa.me` nos arquivos PHP e substitua os links fixos restantes.

---

## 9. Padrao de Entrega Para Nova Empresa

Para cada nova implantacao, entregue:
- pasta do projeto personalizada;
- dump SQL da nova base;
- usuario e senha admin iniciais;
- mini manual com URL publica, rotina de cadastro e rotina de venda.

Isso reduz retrabalho e acelera a entrada em operacao.

---

## 10. Licenca e Uso

Projeto de base customizavel para implantacoes de pequeno porte.

Recomendacao:
- manter este README como documento principal;
- registrar qualquer ajuste especifico da empresa em um arquivo adicional, por exemplo `README_CLIENTE_NOME.md`.

---

## 11. Template Reutilizavel (Outros Projetos)

Para reaproveitar este processo em novos clientes, use o arquivo:
- `README_TEMPLATE_REPLICACAO.md`

Ele foi criado como modelo generico (com placeholders) para copiar e adaptar rapidamente em qualquer projeto semelhante.
