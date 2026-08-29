#!/usr/bin/env bash
 
  set -Eeuo pipefail
 
  cd /var/www/html
 
  echo "Limpando configuração anterior..."
  php artisan config:clear
 
  echo "Executando migrations..."
 
  for attempt in 1 2 3 4 5; do
      if php artisan migrate --force; then
          break
      fi
 
      if [ "$attempt" = "5" ]; then
          echo "Não foi possível executar as migrations após cinco tentativas."
          exit 1
      fi
 
      echo "Banco ainda indisponível. Nova tentativa em cinco segundos..."
      sleep 5
  done
 
  echo "Cadastrando questionário institucional..."
  php artisan db:seed \
      --class='Database\Seeders\QuestionnaireSeeder' \
      --force
 
  echo "Removendo tokens expirados..."
  php artisan sanctum:prune-expired --hours=24
 
  echo "Otimizando Laravel..."
  php artisan optimize
 
  echo "Iniciando servidor web..."
  exec apache2-foreground

  chmod +x glicodata/deploy/render/start.sh