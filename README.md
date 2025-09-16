Guia de Instalação da Aplicação
1. Pré-requisitos

Antes de iniciar, certifique-se de ter instalado em sua máquina:

PHP >= 8.x

Composer

MySQL / MariaDB
 ou outro banco compatível

Node.js e NPM

Git

Opcional:

Docker
 (se desejar rodar via container)

2. Clonar o repositório
 git clone https://github.com/SEU_USUARIO/SEU_REPOSITORIO.git
cd SEU_REPOSITORIO

3. Instalar dependências
PHP / Laravel
composer install

Node / Front-end
npm install
npm run build   # ou npm run dev para ambiente de desenvolvimento

4. Configurar variáveis de ambiente

Copie o arquivo .env.example para .env:

cp .env.example .env


Edite as variáveis conforme seu ambiente:

APP_NAME=MinhaAplicacao
APP_ENV=local
APP_KEY=   # será gerado no próximo passo
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha

5. Gerar a chave da aplicação
php artisan key:generate


Isso define a APP_KEY no seu .env.

6. Configurar banco de dados

Crie o banco de dados conforme o .env e rode as migrations:

php artisan migrate

9. Observações

Se estiver usando Docker, você pode criar um docker-compose.yml para rodar PHP, MySQL e Node juntos.

Certifique-se de dar permissão de escrita para a pasta storage e bootstrap/cache:

chmod -R 775 storage bootstrap/cache
