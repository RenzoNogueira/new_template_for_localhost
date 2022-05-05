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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Localhost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        .list-group-item:hover {
            background-color: #f5f5f5;
        }

        .file-item.bg-light {
            animation: slideInLeft 1s ease-in-out !important;
        }

        .file-item.bg-white {
            animation: slideInRight 1s ease-in-out !important;
        }

        /* Deslizar da esquerda para a direita */
        @keyframes slideInLeft {
            0% {
                opacity: 0;
                transform: translateX(-100%);
            }

            85% {
                opacity: 0;
            }

            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            0% {
                opacity: 0;
                transform: translateX(100%);
            }

            85% {
                opacity: 0;
            }

            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>
    <main id="app" class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>Lista de arquivos e pastas</h3>
                        </div>
                        <div class="card-body">
                            <!-- lista horizontal de favoritos -->
                            <div class="row">
                                <div class="col-md-12">
                                    <h4>Favoritos</h4>
                                    <button v-for="favorite in favorites" class="btn btn-outline-secondary m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" :title="favorite">
                                        <a :href="favorite" target="_blank" class="text-decoration-none text-dark m-2">{{favorite.substring(0, 6)}}...</a>
                                        <!-- Remover -->
                                        <a href="#" @click="removeFavorite(favorite)" class="text-decoration-none text-dark"><i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <!-- Lista em navtabs os diretorios do historiico -->
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li v-for="history in historic" class="nav-item d-flex align-items-center">
                                        <a :href="history.name" class="nav-link text-secondary" :id="history.name" data-toggle="tab" role="tab" aria-controls="home" aria-selected="true" target="_blank"><i :class="history.type"></i> {{ history.name }}</a>
                                    </li>
                                </ul>
                                <ul class="list-group">
                                    <!-- Loading com v-show enquanto files estiver vazio -->
                                    <li class="list-group-item text-center" v-show="files.length === 0">
                                        <div class="spinner-border text-secondary" role="status"></div>
                                    </li>
                                    <li v-for="(file, index) in files" class="list-group-item border-0">
                                        <!-- verifica de o index é par ou impar -->
                                        <div :class="['row','file-item', index % 2 === 0 ? 'bg-light' : 'bg-white']">
                                            <div class="col-md-12 d-flex justify-content-between p-0">
                                                <span>
                                                    <i :class="[file.type, (file.type.includes('fa-folder') ? 'text-warning': 'text-secondary')]"></i> <a @click="setHistory({name:file.name, type:file.type})" :href="file.name" :title="file.name" class="text-decoration-none text-dark" target="_blank">{{ file.name }}</a>
                                                </span>
                                                <button v-show="!favorites.includes(file.name)" @click="addFavorite(file)" class="btn btn-link p-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Adicionar aos favoritos">
                                                    <i class="fas fa-star text-secondary"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer com copyrigth -->
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-footer">
                            <p class="text-center">
                                <small>
                                    <i class="fa fa-copyright"></i>
                                    <span class="px-2">Copyright 2022</span>
                                    <span>-</span>
                                    <span class="px-2">Desenvolvido por</span>
                                    <span>Renzo Nogueira</span>
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
    </main>
    <script src="https://kit.fontawesome.com/274af9ab8f.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://unpkg.com/vue@3"></script>
    <script>
        Vue.createApp({
            data() {
                return {
                    directory: ".",
                    files: [],
                    folders: [],
                    historic: [],
                    favorites: [],
                    fileTypes: {
                        "file": "fas fa-file",
                        "folder": "fas fa-folder",
                        ".php": "fab fa-php",
                        ".js": "fab fa-js",
                        ".css": "fab fa-css",
                        ".html": "fab fa-html5",
                        ".json": "fab fa-js-square",
                        ".txt": "fas fa-file-alt",
                        ".pdf": "fas fa-file-pdf",
                        ".jpg": "fas fa-file-image",
                        ".png": "fas fa-file-image",
                        ".gif": "fas fa-file-image",
                        ".svg": "fas fa-file-image",
                        ".mp3": "fas fa-file-audio",
                        ".mp4": "fas fa-file-video",
                    }
                }
            },
            // Inicialização
            beforeMount: function() {
                
                this.openFolder();
                this.getFavorites();
            },
            methods: {
                setHistory: function(name) {
                    // Veridica se o link já existe no historico
                    if (!this.historic.includes(name)) {
                        this.historic.push(name);
                    }
                },
                openFolder: function(folder = './') {
                    const SELF = this;
                    SELF.directory = folder;
                    // Verifica se o caminho já não existe no historico e adiciona
                    if (SELF.historic.indexOf(folder) === -1) {
                        SELF.historic.push(folder);
                    }
                    SELF.files = [];
                    SELF.folders = [];
                    $.post({
                        url: 'index.php',
                        type: 'POST',
                        data: {
                            directory: JSON.stringify(`${folder}`)
                        },
                        success: function(data) {
                            const dataFiles = JSON.parse(data);

                            Object.keys(dataFiles["folders"]).forEach(function(key) {
                                SELF.files.push({
                                    name: dataFiles["folders"][key],
                                    type: SELF.fileTypes["folder"],
                                });
                            });

                            Object.keys(dataFiles["files"]).forEach(function(key) {
                                Object.keys(SELF.fileTypes).forEach(function(type) {
                                    if (dataFiles["files"][key].includes(type)) {
                                        SELF.files.push({
                                            name: dataFiles["files"][key],
                                            type: SELF.fileTypes[type],
                                        });
                                    }
                                });
                            });
                        }
                    });
                },

                // Função para carregar os favoritos
                getFavorites: function() {
                    const SELF = this;
                    $.post({
                        url: 'index.php',
                        type: 'POST',
                        data: {
                            getFavorites: true
                        }
                    }).done(function(data) {
                        SELF.favorites = JSON.parse(data);
                    });
                },

                // Adiciona um favorito
                addFavorite: function(file) {
                    const SELF = this;
                    $.post({
                        url: 'index.php',
                        type: 'POST',
                        data: {
                            addFavorite: JSON.stringify(`${file.name}`)
                        }
                    }).done(function(data) {
                        SELF.getFavorites();
                    });
                },

                // Remove um favorito
                removeFavorite: function(file) {
                    const SELF = this;
                    $.post({
                        url: 'index.php',
                        type: 'POST',
                        data: {
                            removeFavorite: JSON.stringify(`${file}`)
                        }
                    }).done(function(data) {
                        SELF.getFavorites();
                    });
                }
            },
            mounted: function() {
                window.onload = function() {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })
                }
            }
        }).mount('#app')
    </script>
</body>

</html>