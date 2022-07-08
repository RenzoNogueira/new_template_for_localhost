<?php

// Se não existir o arquivo favorites.json irá cria-lo
$favorites = file_exists('favorites.json') ? json_decode(file_get_contents('favorites.json'), true) : createFavorites();
function createFavorites()
{
  $favorites = [];
  file_put_contents('favorites.json', json_encode($favorites));
};
if (isset($_POST["addFavorite"])) {
  define("NEW_FAVORITE", json_decode($_POST["addFavorite"]));
  if (!in_array(NEW_FAVORITE, $favorites)) { // Verifica se o favorito já não existe no array
    array_push($favorites, json_decode($_POST["addFavorite"], true));
    file_put_contents('favorites.json', json_encode($favorites)); // Salva o arquivo
  }
  die();
} else if (isset($_POST["removeFavorite"])) {
  define("R_FAVORITE", json_decode($_POST["removeFavorite"]));
  if (in_array(R_FAVORITE, $favorites)) { // Verifica se o favorito existe no array
    // Remove do array
    $key = array_search(R_FAVORITE, $favorites);
    unset($favorites[$key]);
    // retira as chaves do array
    $favorites = array_values($favorites);
    file_put_contents('favorites.json', json_encode($favorites)); // Salva o arquivo
  }
  die();
} else if (isset($_POST["directory"])) {
  // Lê os arquivos e pastas da pasta atual e pega os seus tipos
  $directory = json_decode($_POST["directory"]);
  $data = scandir($directory);
  $data = array_diff($data, array('.', '..'));
  $data = array_values($data);
  $data = array_reverse($data);
  $files["files"] = array_filter($data, function ($file) {
    return !is_dir($file);
  });
  $files["folders"] = array_filter($data, function ($folder) {
    return is_dir($folder);
  });
  echo json_encode($files);
  die();
} else if (isset($_POST["getFavorites"])) {
  echo json_encode($favorites);
  die();
}
if (isset($_POST["getUser"])) {
  // pega o nome do usuário logado na máquina
  define("USER", getenv('USERNAME'));
  echo json_encode(USER);
  die();
}
