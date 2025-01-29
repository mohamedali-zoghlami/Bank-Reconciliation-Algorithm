<form  class="forms-sample" onsubmit="addJournal();return false">
    <div class="form-group row">
      <label for="client" class="col-sm-3 col-form-label">Nom du client</label>
      <div class="col-sm-9">
        <input type="text" class="form-control" id="client" placeholder="Nom du client" required>
      </div>
    </div>
    <div class="form-group row">
      <label for="numero" class="col-sm-3 col-form-label">Numéro</label>
      <div class="col-sm-9">
        <input type="number" class="form-control" id="numero" placeholder="Numéro de la facture" required>
      </div>
    </div>
    <div class="form-group row">
        <label for="client" class="col-sm-3 col-form-label">Libellé</label>
        <div class="col-sm-9">
          <input type="text" class="form-control" id="libelleJournal" placeholder="Libellé" required>
        </div>
      </div>
    <div class="form-group row">
      <label for="date" class="col-sm-3 col-form-label">Date</label>
      <div class="col-sm-9">
        <input type="date" class="form-control" id="date" required>
      </div>
    </div>
    <div class="form-group row">
      <label for="montant" class="col-sm-3 col-form-label">Debit </label>
      <div class="col-sm-9">
        <input type="number" class="form-control" id="debit" min="0" placeholder="Debit de la facture" required>
      </div>
    </div>
    <div class="form-group row">
        <label for="montant" class="col-sm-3 col-form-label">Credit </label>
        <div class="col-sm-9">
          <input type="number" class="form-control" id="credit" min="0" placeholder="Crédit de la facture" required>
        </div>
      </div>
    <div class="d-flex justify-content-end mb-3">
        <button type="submit" class="btn btn-primary mr-2">Ajouter</button>
        <button type="button" id="deleteJournal" class="btn btn-danger mr-2">Tout supprimer</button>
    </div>

</form>
