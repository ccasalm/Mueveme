DROP TABLE IF EXISTS noticias CASCADE;

CREATE TABLE noticias
(
    id                   bigserial      PRIMARY KEY
  , titulo               varchar(255)   NOT NULL
  , contenido            varchar(1000)  NOT NULL
  , usuario_id           bigint         NOT NULL REFERENCES usuarios(id) ON DELETE NO ACTION ON UPDATE CASCADE
  , categoria_id         bigint         NOT NULL REFERENCES categorias(id) ON DELETE NO ACTION ON UPDATE CASCADE
  , created_at           timestamptz    DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS usuarios CASCADE;

CREATE TABLE usuarios
(
     id                  bigserial    PRIMARY KEY
   , login               varchar(255) NOT NULL UNIQUE
   , password            varchar(255) NOT NULL
   , email               varchar(255) NOT NULL
   , admin               bool         DEFAULT FALSE
   , created_at          timestamptz  DEFAULT CURRENT_TIMESTAMP

);

DROP TABLE IF EXISTS categorias CASCADE;

CREATE TABLE categorias
(
    id              bigserial    PRIMARY KEY
  , nombre          varchar(255) NOT NULL
);

INSERT INTO usuarios (login, password, email, admin)
VALUES ('pepe', crypt('pepe' ,gen_salt('bf', 12)), 'pepe@pepe.com', FALSE)
      ,('christian', crypt('christian' ,gen_salt('bf', 12)), 'christian@gmail.com', TRUE)
      ,('luis', crypt('luis' ,gen_salt('bf', 12)), 'luis@gmail.com', TRUE);

INSERT INTO categorias (nombre)
VALUES ('Gastronimia')
     , ('Ciencia')
     , ('Medicina');

INSERT INTO noticias (titulo, contenido, usuario_id, categoria_id)
VALUES ('Injusticias en España', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quas tempore veritatis quod reprehenderit libero iste blanditiis, voluptatum sunt praesentium perspiciatis debitis totam expedita culpa sint recusandae? Odit provident officia perspiciatis?
', 1,1)
     , ('Italia y su buena comida', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quas tempore veritatis quod reprehenderit libero iste blanditiis, voluptatum sunt praesentium perspiciatis debitis totam expedita culpa sint recusandae? Odit provident officia perspiciatis?
',2,2)
     , ('Francia no ganará el mundial', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quas tempore veritatis quod reprehenderit libero iste blanditiis, voluptatum sunt praesentium perspiciatis debitis totam expedita culpa sint recusandae? Odit provident officia perspiciatis?
',3,3)
     , ('Injusticias en España', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quas tempore veritatis quod reprehenderit libero iste blanditiis, voluptatum sunt praesentium perspiciatis debitis totam expedita culpa sint recusandae? Odit provident officia perspiciatis?
', 1,1)
     , ('Italia y su buena comida', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quas tempore veritatis quod reprehenderit libero iste blanditiis, voluptatum sunt praesentium perspiciatis debitis totam expedita culpa sint recusandae? Odit provident officia perspiciatis?
',2,2)
     , ('Francia no ganará el mundial', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quas tempore veritatis quod reprehenderit libero iste blanditiis, voluptatum sunt praesentium perspiciatis debitis totam expedita culpa sint recusandae? Odit provident officia perspiciatis?
',3,3);
