# Appermesso

Web app Laravel per compilare ed esportare il modulo di richiesta assenza.

## Avvio locale

```bash
composer install
npm ci
npm run build
php artisan serve
```

## Docker

```bash
docker build -t appermesso .
docker run --rm -p 10000:10000 -e APP_KEY="your-laravel-app-key" appermesso
```

L'applicazione sarà disponibile su `http://localhost:10000`.

## Deploy su Render

Il file `render.yaml` configura un Web Service Docker. Collega il repository a Render usando **New > Blueprint**, quindi imposta:

- `APP_URL` con l'URL pubblico assegnato da Render;
- `APP_KEY` con il risultato locale di `php artisan key:generate --show`.

Se `APP_KEY` non viene fornita, il container ne genera una temporanea all'avvio. Per evitare l'invalidazione delle sessioni a ogni riavvio è consigliato configurarla come variabile segreta persistente.
