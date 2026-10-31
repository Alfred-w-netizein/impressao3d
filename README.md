# PRINT3D — Sistema de Impressão 3D sob demanda

Sistema web em **PHP 8 + MySQL (PDO)** com dois ambientes:
- **Site público** (bio do Instagram): apresentação, possibilidades, projetos, contato WhatsApp e acompanhamento de pedido.
- **Painel admin** (login): gestão de pedidos, comprovantes, extrato de pagamentos e publicação de projetos.

Sem frameworks. HTML/CSS/JS vanilla. Layout mobile-first, estética **brutalista retrô**.

---

## 1. Requisitos

- PHP **8.0+** com extensões: `pdo_mysql`, `fileinfo`, `mbstring`, `iconv`
- MySQL 5.7+ / MariaDB 10.3+
- Apache com `mod_rewrite` (recomendado, mas o sistema funciona sem)
- Permissão de escrita em `public/uploads/`

---

## 2. Estrutura

```
impressao3d/
├── public/                     ← document root
│   ├── index.php, projetos.php, acompanhar.php, pedido.php, contato.php, upload_comprovante.php
│   ├── admin/                  ← painel
│   ├── assets/css/style.css
│   ├── assets/js/app.js
│   └── uploads/                ← protegido por .htaccess
├── app/                        ← código interno (NÃO deve ser exposto)
│   ├── config.php / config.example.php
│   ├── db.php, helpers.php, auth.php, upload.php
│   ├── header.php, footer.php, admin_header.php, admin_footer.php
├── sql/schema.sql
└── README.md
```

> ⚠️ **Importante**: `app/` e `sql/` devem ficar **fora** do document root. Se a hospedagem só permite uma pasta raiz, mova-os para fora ou adicione um `.htaccess` em `app/` com `Require all denied`.

---

## 3. Instalação (hospedagem compartilhada Apache/PHP/MySQL)

### 3.1 Enviar arquivos
1. Crie a pasta do projeto no servidor (ex.: `~/impressao3d/`).
2. Envie `app/`, `sql/`, `README.md` para a raiz do projeto.
3. Envie **todo o conteúdo de `public/`** para `public_html/` (ou `www/`).

### 3.2 Banco de dados
1. No painel MySQL (phpMyAdmin, cPanel, etc.), crie um banco `impressao3d` com `utf8mb4_unicode_ci`.
2. Importe `sql/schema.sql` (ele já cria o banco, mas você pode remover a linha `CREATE DATABASE` se o painel já criou).
3. Crie os 2 admins com senha forte:

```bash
php -r "echo password_hash('SUA_SENHA_FORTE', PASSWORD_DEFAULT), PHP_EOL;"
```

Depois:
```sql
INSERT INTO admins (nome, email, senha_hash) VALUES
('DONO_1','dono1@seudominio.com','<HASH_GERADO>'),
('DONO_2','dono2@seudominio.com','<HASH_GERADO>');
```

> O `schema.sql` traz um exemplo com hash da senha `admin123`. **Troque antes de colocar em produção.**

### 3.3 Configuração
1. Copie `app/config.example.php` → `app/config.php`.
2. Ajuste:
   - `db.user`, `db.pass`, `db.name`
   - `app.base_url` (ex.: `https://print3d.com.br` — sem barra final)
   - `app.upload_dir` (deve apontar para `public/uploads`)
   - `admins.dono1.whats` e `admins.dono2.whats` (formato `55DDDnumero`, só dígitos)
3. Confirme `app.timezone = 'America/Sao_Paulo'`.

### 3.4 Uploads
- Garanta permissão `0755` em `public/uploads/`, `public/uploads/comprovantes/`, `public/uploads/projetos/`.
- Verifique que `public/uploads/.htaccess` está no lugar (bloqueia execução de PHP).
- Se o Apache reclamar de `php_flag engine off`, remova essa linha e mantenha apenas o `<FilesMatch>` — a proteção por MIME já é feita no PHP.

### 3.5 Teste rápido
- Acesse `https://seudominio.com/` → home pública.
- Acesse `https://seudominio.com/admin/login.php` → faça login com um admin.

---

## 4. Fluxo de teste ponta a ponta

### 4.1 Cliente → contato
1. Home → seção **Contato**.
2. Digite nome completo (mínimo duas palavras) → clique em "Abrir WhatsApp" de um dono.
3. Verifique redirecionamento para `wa.me` com mensagem contendo o nome.

### 4.2 Admin → criar pedido
1. Login em `/admin/login.php`.
2. Menu **Novo** → preencha nome do cliente (mesmo nome do passo anterior), descrição, valor (ex.: `150,00`), chave Pix, status inicial.
3. Sistema gera `ORD-2026-0001` automaticamente.

### 4.3 Cliente → acompanhar + comprovante
1. Home → **Acompanhar** → informe o nome completo.
2. Veja o pedido listado → clique em "Detalhes".
3. Copie a chave Pix com o botão (JS).
4. Envie um comprovante (jpg/png/webp/pdf).
5. Status do pedido muda automaticamente para **Comprovante enviado**.

### 4.4 Admin → confirmar pagamento
1. Abra o pedido em `/admin/pedido_detalhe.php?id=X`.
2. Veja o comprovante em "Ver comprovante" (endpoint autenticado, não público).
3. Clique em **Confirmar como pago** → status vira **Pago** e um registro entra em `pagamentos_extrato`.

### 4.5 Extrato + CSV
1. `/admin/extrato.php` → filtro Semana/Mês.
2. Clique em **Exportar CSV** → arquivo com BOM UTF-8 e separador `;` abre corretamente no Excel.

### 4.6 Finalizar e publicar
1. No pedido, mude o status para **Finalizado**.
2. Clique em **Criar projeto publicado**.
3. Envie fotos (múltiplas) e/ou vídeo + descrição → marque **Publicar imediatamente** → salvar.
4. O status do pedido vira **Publicado nos projetos**.
5. Acesse `/projetos.php` → veja a galeria pública **sem** dados sensíveis (nome do cliente, valor, Pix).
6. Em `/admin/projetos_admin.php` → botão **Despublicar** oculta da galeria pública sem apagar nada.

---

## 5. Segurança implementada

- `password_hash` / `password_verify` para admins.
- Sessão com `httponly` + `samesite=Lax`.
- CSRF em **todos** os formulários POST (`csrf_field()` / `csrf_check()`).
- PDO com **prepared statements** em todas as queries com dados de usuário.
- Uploads:
  - Nome aleatório (`bin2hex(random_bytes(16))`).
  - Validação por **extensão + MIME real** (`finfo`).
  - Tamanho máximo por tipo (5 MB imagens, 40 MB vídeo).
  - `.htaccess` bloqueia execução de PHP na pasta.
- Comprovantes **não** ficam públicos: acessados via `admin/ver_comprovante.php` (autenticado).
- Cliente só vê pedidos cujo `nome_normalizado` bate com o nome informado.
- CSRF e validação básica de nome (mínimo duas palavras) no fluxo do cliente.

---

## 6. Decisões assumidas (documentadas)

- **Identificação do cliente**: por `nome_normalizado` (sem acento, minúsculo). Se já existir, é reutilizado — evita duplicidade.
- **Código do pedido**: `ORD-{ano}-{NNNN}` sequencial por ano.
- **Chave Pix**: armazenada como texto livre, editável por pedido (não há chave global).
- **Extrato**: registra valor **no momento** da confirmação; se o pedido for editado depois, o extrato histórico não muda (comportamento contábil).
- **Status**: o admin pode mover livremente; `pago_em`, `finalizado_em`, `publicado_em` só são preenchidos uma vez (`COALESCE`).
- **Projetos publicados**: vinculados 1:1 a um pedido; despublicar = `ativo = 0` (dados preservados).
- **CSV**: `;` como separador + BOM UTF-8 para abrir no Excel em pt-BR.
- **Upload de vídeo**: 40 MB (ajustável em `config.php`). Se o servidor tiver `upload_max_filesize` menor, o PHP rejeita antes — aumente no `php.ini` se precisar.
- **WhatsApp**: `wa.me` com mensagem pré-preenchida (URL-encoded).

---

## 7. Administradores iniciais

Definidos no `sql/schema.sql`:
- **DONO_1** — `dono1@loja.local`
- **DONO_2** — `dono2@loja.local`

Senha inicial sugerida: `admin123` (hash de exemplo no SQL).  
**Ação obrigatória antes de produção:** gerar hash novo com `password_hash` e atualizar via SQL ou phpMyAdmin.

---

## 8. Manutenção

- Backup periódico do banco e de `public/uploads/`.
- Monitorar espaço em disco (vídeos ocupam).
- Para trocar senha de admin:
  ```php
  php -r "echo password_hash('NOVA_SENHA', PASSWORD_DEFAULT);"
  ```
  ```sql
  UPDATE admins SET senha_hash='<NOVO_HASH>' WHERE email='dono1@loja.local';
  ```
- Para limpar uploads órfãos: comparar arquivos em `public/uploads/` com as tabelas `comprovantes` e `projeto_midias`.

---

## 9. Limitações conhecidas

- Sem recuperação de senha (2 admins fixos — reset por SQL).
- Sem paginação nas listas (LIMIT 300 em pedidos; refine se crescer muito).
- Sem gateway Pix automático (manual, conforme escopo).
- CSRF e sessão padrão do PHP; para produção de alto tráfego, considere Redis para sessões.