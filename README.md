# EXPEDISOFT - Backend

<p align="center">
  <img src="https://img.shields.io/badge/PHP-76.3%25-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/Blade-23.1%25-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Blade" />
  <img src="https://img.shields.io/badge/Laravel-Framework-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License" />
</p>

<p align="center">
  Backend do sistema <strong>EXPEDISOFT</strong>, desenvolvido como trabalho de conclusão de curso para apoiar a gestão e a rastreabilidade do carregamento de mercadorias.
</p>

<p align="center">
  <a href="#sobre-o-projeto">Sobre o projeto</a> •
  <a href="#tecnologias">Tecnologias</a> •
  <a href="#estrutura">Estrutura</a> •
  <a href="#como-executar">Como executar</a> •
  <a href="#contribuindo">Contribuindo</a>
</p>

---

## Sobre o projeto

O **EXPEDISOFT** é uma solução voltada para a **gestão e rastreabilidade do carregamento de mercadorias**. Este repositório concentra a camada de backend da aplicação, responsável por regras de negócio, integração com banco de dados e suporte às funcionalidades da plataforma.

## Objetivo

Centralizar os processos necessários para controlar informações de carregamento, acompanhar registros e dar suporte à operação do sistema de forma organizada, segura e escalável.

## Tecnologias

- **PHP**
- **Laravel**
- **Blade**
- **MySQL** ou outro SGBD compatível com o projeto

## Funcionalidades esperadas

- Autenticação e controle de acesso
- Cadastro e gerenciamento de entidades do sistema
- Registro de informações operacionais
- Persistência e consulta de dados
- Estrutura preparada para evolução das regras de negócio

## Estrutura

A organização do projeto segue a convenção padrão do Laravel, com destaque para:

- `app/` — regras de negócio, serviços e modelos
- `routes/` — definição das rotas da aplicação
- `resources/views/` — views Blade
- `database/` — migrations, seeders e factories
- `config/` — configurações da aplicação

## Como executar

> Ajuste os comandos conforme a versão do Laravel e as dependências do projeto.

### Pré-requisitos

- PHP instalado
- Composer instalado
- Banco de dados configurado
- Node.js e npm, caso o frontend do projeto seja compilado localmente

### Instalação

```bash
git clone https://github.com/gabrielseffrin/back-expedisoft.git
cd back-expedisoft
composer install
cp .env.example .env
php artisan key:generate
```

Configure as variáveis de ambiente no arquivo `.env`, especialmente as relacionadas ao banco de dados.

### Executar migrations

```bash
php artisan migrate
```

### Iniciar o servidor local

```bash
php artisan serve
```

## Boas práticas de uso

- Mantenha o arquivo `.env` fora do versionamento
- Atualize migrations com cuidado
- Documente novas regras de negócio no projeto
- Teste alterações antes de publicar em produção

## Contribuindo

Contribuições são bem-vindas. Caso deseje colaborar:

1. Faça um fork do repositório
2. Crie uma branch para sua alteração
3. Realize as mudanças necessárias
4. Abra um Pull Request com uma descrição clara

## Licença

Este projeto não possui uma licença definida no momento.

## Autor

Desenvolvido por **Gabriel Seffrin**.
