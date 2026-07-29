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

## Conteggio utilizzi con Supabase

Ogni PDF generato registra in Supabase esclusivamente nome, cognome, tipologia
del modulo e data/ora dell'utilizzo. Le tipologie possibili sono `assenza`,
`presenza` e `omessa_timbratura`; un singolo modulo può averne più di una. Il
contenuto del modulo non viene inviato.

1. Crea un progetto Supabase.
2. Esegui nel SQL Editor il file
   `supabase/migrations/20260727000000_create_app_usage.sql`. Se la tabella era
   già stata creata con una versione precedente, esegui invece
   `supabase/migrations/20260727000100_add_usage_types_to_app_usage.sql`.
3. Configura su Render le variabili segrete:

```dotenv
SUPABASE_URL=https://<project-ref>.supabase.co
SUPABASE_SECRET_KEY=<secret-key>
```

La secret key deve essere configurata solo sul backend e non deve mai essere
esposta nel codice JavaScript. Se Supabase non è configurato o non è
temporaneamente raggiungibile, il PDF viene comunque generato.

## Account e profilo con Supabase Auth

Login, registrazione e profilo sono opzionali: il modulo e la generazione del
PDF restano disponibili anche in modalità ospite. Gli account usano Supabase
Auth con email e password; il profilo memorizza esclusivamente i sette campi
anagrafici già presenti nel modulo.

1. Esegui nel SQL Editor
   `supabase/migrations/20260729000000_create_profiles.sql`.
   Se la tabella `profiles` è già presente, esegui anche
   `supabase/migrations/20260729000100_allow_incomplete_profiles.sql`: consente
   al trigger di registrazione di creare un profilo incompleto, che verrà poi
   completato dall'utente nella pagina Profilo.
2. Configura anche la chiave anon/publishable usata dal backend:

```dotenv
SUPABASE_ANON_KEY=<anon-or-publishable-key>
```

La chiave viene usata soltanto dal backend Laravel insieme al token
dell'utente; le policy RLS consentono a ogni account di accedere esclusivamente
al proprio profilo. `APP_KEY` deve essere persistente perché protegge anche la
sessione Laravel che contiene i token Supabase. Mantieni inoltre
`SESSION_ENCRYPT=true` in ogni ambiente.

Per consentire l'accesso immediatamente dopo la registrazione, disattiva
**Confirm email** nelle impostazioni Auth di Supabase. Aggiungi inoltre
`<APP_URL>/password/reimposta` agli URL di redirect consentiti.

Il recupero password server-side richiede che il template email **Reset
Password** punti al callback con il token hash, perché il fragment del link
standard non viene inviato al server:

```html
<a href="{{ .RedirectTo }}?token_hash={{ .TokenHash }}&type=recovery">
  Reimposta password
</a>
```

Laravel verifica il token con Supabase prima di mostrare il form di nuova
password. Non inserire access token nella query string.

Il totale degli utilizzi e il dettaglio giornaliero possono essere consultati
nel SQL Editor:

```sql
select count(*) as total_usage
from public.app_usage;

select used_at::date as day, count(*) as usage_count
from public.app_usage
group by used_at::date
order by day desc;

select usage_type, count(*) as usage_count
from public.app_usage
cross join lateral unnest(usage_types) as usage_type
group by usage_type
order by usage_type;
```
