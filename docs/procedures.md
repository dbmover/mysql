# Procedures
MySQL-specific plugin for (re)creating procedures.

## Caveats
You might have included delimiters in your schema, so you can easily copy/paste
procedure definitions into the MySQL shell. _You can remove them_. They're only
needed because the shell is silly. In fact, they trip DbMover up.

Your procedures can simply be written as:

```sql
CREATE FUNCTION foo()
BEGIN
    -- SQL...
END;
```

