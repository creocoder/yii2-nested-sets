/**
 * SQLite
 */

DROP TABLE IF EXISTS "tree";

CREATE TABLE "tree" (
  "id"    INTEGER NOT NULL PRIMARY KEY,
  "lft"   INTEGER NOT NULL,
  "rgt"   INTEGER NOT NULL,
  "depth" INTEGER NOT NULL,
  "name"  TEXT    NOT NULL
);

DROP TABLE IF EXISTS "multiple_tree";

CREATE TABLE "multiple_tree" (
  "id"    INTEGER NOT NULL PRIMARY KEY,
  "tree"  INTEGER,
  "lft"   INTEGER NOT NULL,
  "rgt"   INTEGER NOT NULL,
  "depth" INTEGER NOT NULL,
  "name"  TEXT    NOT NULL
);
