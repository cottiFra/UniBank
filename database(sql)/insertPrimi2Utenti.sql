INSERT INTO utenti (username, password, email, ruolo, id_universita, id_facolta, data_iscrizione)
VALUES ('admin', '$2y$10$Wxh35GlPm/aTywIJLR6zGeYd2V8DVoiS2aVF5VUD547SgD1ZZ5rZy', 'admin@unibank.it', 1, 1, 1, NOW());

INSERT INTO utenti (username, password, email, ruolo, id_universita, id_facolta, data_iscrizione)
VALUES ('utente', '$2y$10$f/GDZWp7VDOMCjHoeUtNTOBTzHPSAV38Lau3p6S47JVNaUzVpxdAa', 'utente@email.it', 0, 1, 1, NOW());