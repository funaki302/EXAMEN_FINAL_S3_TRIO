-- ==========================================
-- CRATION DE LA TABLE DES MODES
-- ==========================================

CREATE TABLE IF NOT EXISTS BNGRC_modes (
    id_mode INT AUTO_INCREMENT PRIMARY KEY,
    nom_mode VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255)
) ENGINE=InnoDB;

-- Insertion des modes par dfaut
INSERT INTO BNGRC_modes (id_mode, nom_mode, description) VALUES 
(1, 'origine', 'Donnes de production/origine - ne seront jamais supprimes'),
(2, 'teste', 'Donnes de test - peuvent être supprimes lors de la rinitialisation')
ON DUPLICATE KEY UPDATE nom_mode = VALUES(nom_mode);

-- ==========================================
-- AJOUT DE LA COLONNE id_mode DANS LES TABLES CONCERNES
-- ==========================================

-- Table BNGRC_besoins_villes
ALTER TABLE BNGRC_besoins_villes 
ADD COLUMN IF NOT EXISTS id_mode INT NOT NULL DEFAULT 2,
ADD CONSTRAINT fk_besoins_mode FOREIGN KEY (id_mode) REFERENCES BNGRC_modes(id_mode);

-- Table BNGRC_dons_recus
ALTER TABLE BNGRC_dons_recus 
ADD COLUMN IF NOT EXISTS id_mode INT NOT NULL DEFAULT 2,
ADD CONSTRAINT fk_dons_mode FOREIGN KEY (id_mode) REFERENCES BNGRC_modes(id_mode);

-- Table BNGRC_distributions
ALTER TABLE BNGRC_distributions 
ADD COLUMN IF NOT EXISTS id_mode INT NOT NULL DEFAULT 2,
ADD CONSTRAINT fk_distributions_mode FOREIGN KEY (id_mode) REFERENCES BNGRC_modes(id_mode);

-- Table BNGRC_achats
ALTER TABLE BNGRC_achats 
ADD COLUMN IF NOT EXISTS id_mode INT NOT NULL DEFAULT 2,
ADD CONSTRAINT fk_achats_mode FOREIGN KEY (id_mode) REFERENCES BNGRC_modes(id_mode);

-- Table BNGRC_transactions_argent
ALTER TABLE BNGRC_transactions_argent 
ADD COLUMN IF NOT EXISTS id_mode INT NOT NULL DEFAULT 2,
ADD CONSTRAINT fk_transactions_mode FOREIGN KEY (id_mode) REFERENCES BNGRC_modes(id_mode);

-- ==========================================
-- PROCDURE DE RINITIALISATION DES DONNES DE TEST
-- ==========================================

DELIMITER //

DROP PROCEDURE IF EXISTS sp_reinitialiser_donnees_test //

CREATE PROCEDURE sp_reinitialiser_donnees_test()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur lors de la rinitialisation des donnes de test';
    END;
    
    START TRANSACTION;
    
    -- Rcuprer l'id du mode 'teste'
    SET @id_mode_teste = (SELECT id_mode FROM BNGRC_modes WHERE nom_mode = 'teste');
    
    -- Supprimer les distributions de test
    DELETE FROM BNGRC_distributions WHERE id_mode = @id_mode_teste;
    
    -- Supprimer les transactions argent de test
    DELETE FROM BNGRC_transactions_argent WHERE id_mode = @id_mode_teste;
    
    -- Supprimer les achats de test
    DELETE FROM BNGRC_achats WHERE id_mode = @id_mode_teste;
    
    -- Supprimer les dons de test
    DELETE FROM BNGRC_dons_recus WHERE id_mode = @id_mode_teste;
    
    -- Supprimer les besoins de test
    DELETE FROM BNGRC_besoins_villes WHERE id_mode = @id_mode_teste;
    
    COMMIT;
    
    SELECT 'Rinitialisation des donnes de test effectue avec succès' AS message;
END //

DELIMITER ;

-- ==========================================
-- VUE POUR OBTENIR LES STATISTIQUES PAR MODE
-- ==========================================

CREATE OR REPLACE VIEW BNGRC_V_Stats_Par_Mode AS
SELECT 
    m.id_mode,
    m.nom_mode,
    m.description,
    (SELECT COUNT(*) FROM BNGRC_besoins_villes WHERE id_mode = m.id_mode) AS nb_besoins,
    (SELECT COUNT(*) FROM BNGRC_dons_recus WHERE id_mode = m.id_mode) AS nb_dons,
    (SELECT COUNT(*) FROM BNGRC_distributions WHERE id_mode = m.id_mode) AS nb_distributions,
    (SELECT COUNT(*) FROM BNGRC_achats WHERE id_mode = m.id_mode) AS nb_achats,
    (SELECT COUNT(*) FROM BNGRC_transactions_argent WHERE id_mode = m.id_mode) AS nb_transactions
FROM BNGRC_modes m;
