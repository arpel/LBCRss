<?php
// vérifie la possibilité d'effectuer des requêtes distantes
$error = false;

if (!empty($_POST["selectproxy"])) {
    $findproxystatus = $getC->findbestproxy("http://www.google.com");
}

if (!$getC->file_get_contents('http://www.leboncoin.fr/')) {
    $error = true;
}

$values = array(
    "url" => "", "price_min" => "", "price_max" => "", "price_strict" => false,
    "cities" => ""
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $values = array_merge($values, array_map("trim", $_POST));
    if (empty($values["url"])) {
        $errors["url"] = "Ce champ est obligatoire.";
    }
    if ($values["price_min"] && $values["price_min"] != (int)$values["price_min"]) {
        $errors["price"] = "Valeur de \"prix min\" non valide. ";
    }
    if ($values["price_max"] && $values["price_max"] != (int)$values["price_max"]) {
        $errors["price"] .= "Valeur de \"prix max\" non valide.";
    }
    if (empty($errors)) {
        $query = array("url" => $values["url"]);
        if (!empty($values["price_min"])) {
            $query["price_min"] = (int)$values["price_min"];
        }
        if (!empty($values["price_max"])) {
            $query["price_max"] = (int)$values["price_max"];
        }
        if (!empty($values["cities"])) {
            $query["cities"] = $values["cities"];
        }
        if (!empty($values["multipleURLs"])) {
            $query["multipleURLs"] = $values["multipleURLs"];
        }
        if (!empty($values["queryname"])) {
            $query["queryname"] = $values["queryname"];
        }
        $query["price_strict"] = isset($values["price_strict"])?
            (int)(bool)$values["price_strict"]:0;
        header("LOCATION: ./?".http_build_query($query));
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Flux RSS des annonces Leboncoin</title>
        <link rel="stylesheet" href="main.css" type="text/css">
        <meta charset="utf-8">
    </head>
    <body>
        <h1>Flux RSS des annonces Leboncoin</h1>
        <?php if ($error) : ?>
        <p style="width: 600px; font-weight: bold; color: #EF0000;">
            Les connexions distantes ne semblent pas actives sur cet hébergement.
            Il ne sera pas possible de générer les flux RSS.
        </p>
        <?php endif; ?>
        <?php if ($findproxystatus) : ?>
            <p style="width: 600px;"><?php echo $findproxystatus ?></p>
        <?php endif; ?>
        <div id="stylized" class="myform">
        <form action="" method="post" style="width: 600px;">
            <fieldset>
                <legend>Génération d'un flux RSS</legend>
                <dl>

                    <dt><label for="queryname">
                        Indiquer un nom pour cette recherche</label></dt>
                    <dd><input type="text" id="queryname" name="queryname" value="<?php
                        echo $values["queryname"]; ?>" size="75" /></dd>

                    <dt><label for="url">
                        Indiquer l'adresse de recherche Leboncoin</label></dt>
                    <dd><input type="text" id="url" name="url" value="<?php
                        echo $values["url"]; ?>" size="125" /></dd>
                    <dt>
                        <label>Filtre sur le prix</label>
                    <dt>
                    <dd>
                        <label for="price_min">
                            Prix min :
                            <input type="text" id="price_min" name="price_min" value="<?php
                                echo $values["price_min"]; ?>" size="6" />
                        </label>
                        <label for="price_max">
                            Prix max :
                            <input type="text" id="price_max" name="price_max" value="<?php
                                echo $values["price_max"]; ?>" size="6" />
                        </label>
                        <?php if (isset($errors["price"])) : ?>
                        <p class="error"><?php echo $errors["price"]; ?></p>
                        <?php endif; ?>
                        <br />
                        <label for="price_strict"><input type="checkbox"
                            id="price_strict" name="price_strict" value="1"<?php
                            echo $values["price_strict"]?' checked="checked"':"";
                        ?> /> cochez cette case pour exclure les annonces sans prix d'indiqué de votre recherche.</label>
                    </dd>
                    <dt><label for="cities">Filtre sur les villes (une par ligne)</label></dt>
                    <dd>
                        <textarea id="cities" name="cities" cols="30" rows="10"><?php
                            echo htmlspecialchars($values["cities"]) ?></textarea>
                    </dd>
                    <dt>&nbsp;</dt>

                    <dt><label for="multipleURLs">Recherches a ajouter a la recheche principale</label></dt>
                    <dd>
                        <textarea id="multipleURLs" name="multipleURLs" cols="125" rows="10"><?php
                            echo htmlspecialchars($values["multipleURLs"]) ?></textarea>
                    </dd>
                    <dt>&nbsp;</dt>

                    <dd><input type="submit" value="Enregistrer" /></dd>
                </dl>
            </fieldset>
        </form>
    </div>

    <div id="stylized" class="myform">
        <form action="" method="post" style="width: 600px;">
            <fieldset>
                <legend>Sélectionner le meilleur Proxy</legend>
                <input type="hidden" id="selectproxy" name="selectproxy" value="1"/>
                <input type="submit" value="Find proxy" />
            </fieldset>
        </form>
    </div>
    </body>
</html>
