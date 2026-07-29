create table public.profiles (
    id uuid primary key references auth.users (id) on delete cascade,
    nome text check (nome is null or char_length(nome) between 1 and 100),
    cognome text check (cognome is null or char_length(cognome) between 1 and 100),
    matricola text check (matricola is null or char_length(matricola) <= 50),
    centro_costo text check (centro_costo is null or char_length(centro_costo) <= 100),
    livello text check (livello is null or livello in ('D2', 'C2', 'C3', 'B1')),
    qualifica text check (qualifica is null or qualifica in ('Operaio', 'Impiegato')),
    ente text check (ente is null or char_length(ente) <= 150),
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

alter table public.profiles enable row level security;

revoke all on table public.profiles from anon;
grant select, insert, update on table public.profiles to authenticated;

create policy "Users can read their own profile"
on public.profiles for select
to authenticated
using ((select auth.uid()) = id);

create policy "Users can insert their own profile"
on public.profiles for insert
to authenticated
with check ((select auth.uid()) = id);

create policy "Users can update their own profile"
on public.profiles for update
to authenticated
using ((select auth.uid()) = id)
with check ((select auth.uid()) = id);

create or replace function public.set_profile_updated_at()
returns trigger
language plpgsql
set search_path = ''
as $$
begin
    new.updated_at = now();
    return new;
end;
$$;

create trigger profiles_set_updated_at
before update on public.profiles
for each row execute function public.set_profile_updated_at();
