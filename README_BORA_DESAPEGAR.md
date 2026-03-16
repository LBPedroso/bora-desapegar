# Bora Desapegar - Guia Rapido

## 1. O que ja foi adaptado

- Configuracao principal para o projeto Bora Desapegar.
- Novo banco alvo: `bora_desapegar`.
- Modulos novos em MVC:
  - `Peca` (cadastro e estoque)
  - `Venda` (registro de venda e atualizacao de status)
- Novas telas administrativas:
  - `admin/index.php` (dashboard)
  - `admin/pecas.php`
  - `admin/vendas.php`
- Rodape com assinatura: `Sistema desenvolvido por LBPStartWeb`.

## 2. Subir banco de dados

Com MySQL ativo no XAMPP, rode:

```bash
C:\xampp\mysql\bin\mysql -u root -e "SOURCE C:/xampp/htdocs/desapega/database/schema_bora_desapegar.sql"
C:\xampp\mysql\bin\mysql -u root -e "SOURCE C:/xampp/htdocs/desapega/database/seed_bora_desapegar.sql"
```

## 3. Credenciais iniciais

- URL admin: `http://localhost/desapega/admin/`
- Email: `admin@boradesapegar.com`
- Senha: `admin123`

## 4. Fluxo do MVP

1. Entrar no painel admin.
2. Cadastrar pecas em `Pecas`.
3. Registrar venda em `Vendas`.
4. Conferir indicadores no dashboard.

## 5. Proxima fase sugerida

- Adaptar paginas publicas (home, pecas/catalogo, contato e sobre) para o novo dominio de brecho.
- Remover ou arquivar modulos legados de delivery quando o MVP estiver validado.
