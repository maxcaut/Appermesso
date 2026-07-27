create table public.app_usage (
    id bigint generated always as identity primary key,
    first_name text not null check (char_length(first_name) <= 100),
    last_name text not null check (char_length(last_name) <= 100),
    usage_types text[] not null check (
        cardinality(usage_types) > 0
        and usage_types <@ array['assenza', 'presenza', 'omessa_timbratura']::text[]
    ),
    used_at timestamptz not null default now()
);

alter table public.app_usage enable row level security;

revoke all on table public.app_usage from anon, authenticated;
grant insert, select on table public.app_usage to service_role;
grant usage, select on sequence public.app_usage_id_seq to service_role;

create index app_usage_used_at_index on public.app_usage (used_at desc);
