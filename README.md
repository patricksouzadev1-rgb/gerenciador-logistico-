# Gerenciador Logístico

Aplicação web em PHP para controlar fretes: cadastro de produtos, endereços (origem e destino) e transportadores, geração de fretes e relatório diário.
Projeto da disciplina **Fundamentos de Programação para Internet** (UNIPAR EAD).

## Funcionalidades
- Cadastro, edição e exclusão de **produtos**, **endereços** e **transportadores**
- Geração de **frete**: um transportador leva vários produtos (relação 1 para N), com campos de produto adicionados dinamicamente via JavaScript
- **Relatório** de fretes por dia, com download em CSV
- Menu de navegação e interface responsiva com Bootstrap 5

## Tecnologias
PHP (PDO) · MySQL · HTML · CSS · JavaScript · Bootstrap 5 · Git/GitHub

## Estrutura
| Arquivo | Função |
|---|---|
| `banco.sql` | Criação do banco e das tabelas |
| `config.php` | Conexão com o banco, menu e funções comuns |
| `index.php` | Página inicial com totais |
| `produtos.php`, `enderecos.php`, `transportadores.php` | Cadastro e edição |
| `fretes.php` | Geração e listagem de fretes |
| `relatorio.php` | Relatório por dia e download CSV |

## Como executar
1. Instale o [XAMPP](https://www.apachefriends.org/) e inicie **Apache** e **MySQL**.
2. Copie esta pasta para `C:\xampp\htdocs\gerenciador-logistico`.
3. Abra `http://localhost/phpmyadmin`, vá em **Importar** e selecione `banco.sql`.
4. Acesse `http://localhost/gerenciador-logistico/`.

Se o MySQL tiver senha, ajuste usuário e senha em `config.php`.

## Autor
Seu nome Patrick Souza — RA 09059675 — Curso ADS — Polo Unipar Cascavel
# gerenciador-logistico-