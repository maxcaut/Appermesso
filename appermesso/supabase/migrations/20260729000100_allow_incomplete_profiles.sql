-- La registrazione richiede soltanto email e password. Un eventuale trigger
-- collegato ad auth.users deve quindi poter creare un profilo ancora incompleto.
-- L'applicazione continua a richiedere nome e cognome prima del salvataggio.
alter table public.profiles
    alter column nome drop not null,
    alter column cognome drop not null;

alter table public.profiles
    drop constraint if exists profiles_nome_check,
    drop constraint if exists profiles_cognome_check;

alter table public.profiles
    add constraint profiles_nome_check
        check (nome is null or char_length(nome) between 1 and 100),
    add constraint profiles_cognome_check
        check (cognome is null or char_length(cognome) between 1 and 100);
