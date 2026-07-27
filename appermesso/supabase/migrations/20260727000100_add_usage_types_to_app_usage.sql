alter table public.app_usage
add column if not exists usage_types text[];

do $$
begin
    if not exists (
        select 1
        from pg_constraint
        where conname = 'app_usage_usage_types_check'
          and conrelid = 'public.app_usage'::regclass
    ) then
        alter table public.app_usage
        add constraint app_usage_usage_types_check check (
            usage_types is null
            or (
                cardinality(usage_types) > 0
                and usage_types <@ array['assenza', 'presenza', 'omessa_timbratura']::text[]
            )
        );
    end if;
end
$$;

comment on column public.app_usage.usage_types is
'Tipologie del modulo generato. NULL indica un record precedente all’introduzione della classificazione.';
