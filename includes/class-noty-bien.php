<?php

class Noty_Bien {
    public $nature = '';
    public $ville = '';
    public $code_postal = '';
    public $transaction = '';
    public $transaction_type = '';

    public $transaction_string = '';

    public $surface = '';
    public $surface_string = '';
    public $pieces = '';
    public $chambres = '';

    public $prix = '';
    public $prix_hni = '';
    public $prix_nv = '';
    public $loyer = '';
    public $loyer_periodicite = '';

    public $charges_incluses = '';
    public $montant_charges = '';
    public $montant_etat_lieux = '';
    public $meuble = '';
    public $montant_depot_garantie = '';

    public $type_honoraires = '';
    public $honoraires = '';
    public $honoraires_pourcentage = '';
    public $charges_copropriete = '';
    public $frais_acte = '';

    public $bouquet = '';
    public $bouquet_hni = '';
    public $bouquet_nv = '';
    public $rente = null;

    public $dpe_classe = '';
    public $dpe_value = '';
    public $ges_classe = '';
    public $ges_value = '';

    public $dpe = null;
    public $ges = null;
    public $gse = null;

    public $prix_ou_loyer = '';
    public $localisation = '';
    public $caracteristiques = '—';
    public $subtitle = '';
    public $charges_resume = '';

    public $details = array();
    public $resume_details = array();
}
