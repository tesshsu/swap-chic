<div class="step step-optional locked">
    <p class="h2">Informations supplémentaires (optionnelles)</p>

    <p>Ces options ne sont pas nécessaire à l'enregistrement du produit, cependant nous te conseillons fortement de les saisir, de cette manière, ton produit apparaitra plus souvent lors des recherches de nos membres.<br><br>Tu peux toujours les saisir ulterieurement en éditant le produit dans ton dressing</p>

    <p class="h3">Indique sa couleur principale :</p>
    <select name="couleur" id="addproduct-couleur">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5dea219f73216')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>

    <p class="h3">Indique le motif de son imprimé :</p>
    <select name="imprime" id="addproduct-imprime">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5e0f3060688f0')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>

    <p class="h3">Indique sa matière principale :</p>
    <select name="matiere" id="addproduct-matiere">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5e0f2efd94455')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>

    <p class="h3">Indique la ou les saisons idéales pour porter ton produit :</p>
    <label for="addproduct-printemps"><input type="checkbox" value="printemps" name="saison[]" id="addproduct-printemps">Printemps</label>
    <label for="addproduct-été"><input type="checkbox" value="ete" name="saison[]" id="addproduct-été">Été</label>
    <label for="addproduct-automne"><input type="checkbox" value="automne" name="saison[]" id="addproduct-automne">Automne</label>
    <label for="addproduct-hiver"><input type="checkbox" value="hiver" name="saison[]" id="addproduct-hiver">Hiver</label>

    <p class="h3">Si tu as des détails à ajouter, indique les ci-dessous :</p>
    <textarea name="desc" id="addproduct-desc"></textarea>
    
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>