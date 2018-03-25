# Triggers
MySQL-specific DbMover plugin to drop and recreate all triggers.

## Caveats
Like with procedures, there is no need to explicitly add delimiters in your
schema (as you might have done previously for ease of copy/pasting). Just write
your triggers as follows:

```sql
CREATE TRIGGER name AFTER [OPERATION] ON table FOR EACH ROW
BEGIN
    -- SQL...
END;
```

