# BiblioTech - Sistema de Gestão de Biblioteca

## Descrição do Projeto
O BiblioTech é um sistema web desenvolvido para automatizar a gestão de bibliotecas. Ele controla o acervo de livros, o cadastro de membros, e o fluxo de empréstimos e reservas, aplicando regras de negócio como cálculo automático de multas por atraso.
## Tecnologias
* **Back-end:** PHP 8+ e framework Laravel
* **Base de Dados:** MariaDB / MySQL
* **Versionamento:** Git e GitHub
* **Front-end:** Tailwind/Blade

## Estrutura do Repositório
* `/docs`: Contém a documentação técnica (Diagrama DER e Dicionário de Dados).
* `/app`: Contém o código-fonte da aplicação.
* `/database/migrations`: Contém a estrutura do Banco de Dados (SQL).


## Passo a Passo para Rodar o Projeto
**Clonar o Repositório**
'bash
   git clone [https://github.com/SEU-USUARIO/BiblioTech.git](https://github.com/Joaiiq/BiblioTech.git)'

**Entrar na pasta do projeto**
'cd BiblioTech'

**Instalar as dependências php**
'composer install'

**Configurar a base de dados**
'DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE= bibliotech_db
DB_USERNAME=bibliotech
DB_PASSWORD=12345678'

**Gere a chave de segurança do laravel**
'php artisan key:generate'

**Rode o migrate para criar as tabelas**
'php artisan migrate'

**Inicie o servidor local**
'php artisan serve'

## Equipe 
(Gerente)
Levy Barbosa
(Back-end)
*João felipe
(Front-end)
*Francisco Caio
(Auxiliar)
*João Pedro lobo
*Jarina Keylla