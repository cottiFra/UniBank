CREATE DATABASE IF NOT EXISTS UniBank;
USE UniBank;

CREATE TABLE universita(
    id_universita INT(11) AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    citta_sede VARCHAR(255) NOT NULL
);

CREATE TABLE facolta(
    id_facolta INT(11) AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL
);

CREATE TABLE materia(
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL
);

CREATE TABLE materiaperfacolta(
    id_materiaperfacolta INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_materia INT(11) NOT NULL,
    id_facolta INT(11) NOT NULL,
    CONSTRAINT fk_categoria_materia FOREIGN KEY (id_materia) REFERENCES materia(id_materia),
    CONSTRAINT fk_categoria_facolta FOREIGN KEY (id_facolta) REFERENCES facolta(id_facolta),
    UNIQUE (id_materia, id_facolta)
);

CREATE TABLE utenti(
    id_utente INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    ruolo BOOLEAN NOT NULL, --1 admin 0 utente normale
    bloccato BOOLEAN NOT NULL DEFAULT 0, --0 non bloccato 1 bloccato
    saldo INT NOT NULL DEFAULT 20 CHECK (saldo >= 0), --saldo iniziale a 20 per dare la possibilita di acquistare almeno una dispensa
    data_iscrizione DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_universita INT(11) NOT NULL,
    id_facolta INT(11) NOT NULL,
    CONSTRAINT fk_utente_universita FOREIGN KEY (id_universita) REFERENCES universita(id_universita),
    CONSTRAINT fk_utente_facolta FOREIGN KEY (id_facolta) REFERENCES facolta(id_facolta)
);

CREATE TABLE dispense(
    id_dispensa INT(11) AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(255) NOT NULL,
    descrizione VARCHAR(255) NOT NULL,
    prezzo INT(4) NOT NULL CHECK (prezzo >= 0),
    percorso_file VARCHAR(255) NOT NULL,
    data_caricamento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    approvata BOOLEAN NOT NULL DEFAULT 0,
    bloccata BOOLEAN NOT NULL DEFAULT 0,
    id_utente INT(11) NOT NULL,
    id_materiaperfacolta INT(11) NOT NULL,
    CONSTRAINT fk_dispensa_utente FOREIGN KEY (id_utente) REFERENCES utenti(id_utente),
    CONSTRAINT fk_dispensa_categoria FOREIGN KEY (id_materiaperfacolta) REFERENCES materiaperfacolta(id_materiaperfacolta)
);

CREATE TABLE acquisti(
    id_acquisto INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_utente INT(11) NOT NULL,
    id_dispensa INT(11) NOT NULL,
    data_acquisto DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_acquisto_utente FOREIGN KEY (id_utente) REFERENCES utenti(id_utente),
    CONSTRAINT fk_acquisto_dispensa FOREIGN KEY (id_dispensa) REFERENCES dispense(id_dispensa),
    UNIQUE (id_utente, id_dispensa)
);

CREATE TABLE likes(
    id_like INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_dispensa INT(11) NOT NULL,
    id_utente INT(11) NOT NULL,
    data_like DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_like_dispensa FOREIGN KEY (id_dispensa) REFERENCES dispense(id_dispensa),
    CONSTRAINT fk_like_utente FOREIGN KEY (id_utente) REFERENCES utenti(id_utente),
    UNIQUE(id_dispensa,id_utente)
);