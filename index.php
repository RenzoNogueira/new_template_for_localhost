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
        html {
            --background_1: 'none',
                --background_2: 'none',
                --background_3: 'none',
                --background_4: 'none',
                --text: '#000000'
        }

        /* TEMAS */
        body {
            background-color: var(--background_1);
            transition: background-color 1s;
        }

        a,
        button,
        input,
        textarea,
        p,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        span {
            color: var(--text) !important;
            transition: background-color 1s;
        }

        .card-header {
            background-color: var(--background_2);
            transition: background-color 1s;
        }

        .card-body {
            background-color: var(--background_4);
            transition: background-color 1s;
        }

        .card-footer {
            background-color: var(--background_3);
            transition: background-color 1s;
        }

        .list-group-item:hover {
            background-color: #f5f5f5;
            transition: background-color 1s;
        }

        /* TEMAS */

        .file-item.bg-light {
            animation: slideInLeft 1s ease-in-out !important;
        }

        .file-item.bg-white {
            animation: slideInRight 1s ease-in-out !important;
        }

        .element-item:hover {
            filter: brightness(0.9);
            transform: scale(1.009);
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

        .alert {
            animation: alertFall 1s ease-in-out !important;
        }

        /* Animação de surgimento do alerta */
        @keyframes alertFall {
            from {
                opacity: 0;
                transform: translateY(-100%);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-theme {
            position: relative;
            animation: btn-theme-animation 1s ease-in-out !important;
        }

        /* btn-theme surge subindo girando em 360 graus  */
        @keyframes btn-theme-animation {
            0% {
                opacity: 0;
                transform: rotate(0deg);
                transform: translateY(30%);
            }

            50% {
                transform: rotate(660deg);
            }

            100% {
                opacity: 1;
                transform: rotate(660deg);
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <main id="app" class="py-4">
        <div class="container position-relative">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3>Lista de arquivos e pastas</h3>
                            <!-- Icone de sol  -->
                            <i class="fas fa-sun fa-2x fs-5 float-right text-dark btn-theme light" role="button" data-bs-toggle="tooltip" data-bs-placement="left" title="Alternar tema"></i>
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
                                    <li v-for="(file, index) in files" class="list-group-item element-item border-0">
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

            <div class="position-fixed" id="alerts" style="bottom: 30px; right: 20px;"></div>
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
                    userName: null,
                    alert: false,
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
                        ".py": "fab fa-python",
                        ".txt": "fas fa-file-alt",
                        ".pdf": "fas fa-file-pdf",
                        ".jpg": "fas fa-file-image",
                        ".png": "fas fa-file-image",
                        ".gif": "fas fa-file-image",
                        ".svg": "fas fa-file-image",
                        ".mp3": "fas fa-file-audio",
                        ".mp4": "fas fa-file-video",
                        ".zip": "fas fa-file-archive",
                        ".rar": "fas fa-file-archive",
                        ".7z": "fas fa-file-archive",
                        ".tar": "fas fa-file-archive",
                        ".gz": "fas fa-file-archive",
                        ".bz2": "fas fa-file-archive",
                        ".xz": "fas fa-file-archive",
                        ".iso": "fas fa-file-archive",
                        ".doc": "fas fa-file-word",
                        ".docx": "fas fa-file-word",
                        ".xls": "fas fa-file-excel",
                        ".xlsx": "fas fa-file-excel",
                        ".ppt": "fas fa-file-powerpoint",
                        ".pptx": "fas fa-file-powerpoint"
                    },
                    themes: {
                        light: {
                            background_1: 'none',
                            background_2: 'none',
                            background_3: 'none',
                            background_4: 'none',
                            text: '#000000',
                        },
                        dark: {
                            background_1: '#181818',
                            background_2: '#2f2f2f',
                            background_3: '#474747',
                            background_4: '#5e5e5e',
                            text: '#ffffff',
                        }
                    }
                }
            },
            // Inicialização
            beforeMount: function() {
                this.getUser()
                this.openFolder();
                this.getFavorites();
                // Rechama a função a cada 1 min
                setInterval(() => {
                    this.openFolder();
                }, 60000);
            },
            watch: {
                alert: function(value) {
                    if (!value) {
                        $(".alert").fadeTo(1000, 0.5).slideUp(500, function() {
                            $(this).remove();
                        });
                    }
                }
            },

            methods: {
                newAlert: function(text, type) {
                    const idCount = $(".alert").length; // Conta quantos alertas existem
                    $("#alerts").append(`<div id="alert-${idCount}" class="alert alert-${type}">${text}</div>`);
                    // conta 3 seguntos
                    setTimeout(() => {
                        $(`#alert-${idCount}`).fadeTo(1000 * (idCount / 2), 0.5).slideUp(500, function() {
                            $(this).remove();
                        });
                    }, 3000);
                },

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
                                fileTypeExist = false;
                                Object.keys(SELF.fileTypes).forEach(function(type) {
                                    existItem = false;
                                    for (let i = 0; i < SELF.files.length; i++) {
                                        if (SELF.files[i].name === dataFiles["files"][key]) {
                                            existItem = true;
                                            break;
                                        }
                                    }
                                    if (!existItem) {
                                        if (dataFiles["files"][key].includes(type)) {
                                            console.log(type);
                                            SELF.files.push({
                                                name: dataFiles["files"][key],
                                                type: SELF.fileTypes[type],
                                            });
                                            fileTypeExist = true;
                                            return;
                                        }
                                    }
                                });
                                if (!fileTypeExist) {
                                    SELF.files.push({
                                        name: dataFiles["files"][key],
                                        type: SELF.fileTypes["file"],
                                    });
                                }
                            });
                        }
                    });
                },

                getUser: function() {
                    const SELF = this;
                    $.post({
                        url: 'index.php',
                        type: 'POST',
                        data: {
                            getUser: true
                        },
                        success: function(data) {
                            SELF.userName = JSON.parse(data);
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
                },
                setTheme: function(theme) {
                    const SELF = this;
                    SELF.themes[theme].background;
                    SELF.themes[theme].text;

                    Object.keys(SELF.themes[theme]).map(function(key) {
                        $(`html`).css(`--${key}`, SELF.themes[theme][key]);
                    });
                }
            },
            mounted: function() {
                const SELF = this;
                window.onload = function() {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })
                }

                setTimeout(() => {
                    this.newAlert(`Seja bem vindo ${this.userName}!`, "success")
                    this.newAlert("A lista será atualizada daqui <b>1</b> minuto.", "info")
                    if (sessionStorage.getItem("theme") == "dark") {
                        SELF.setTheme(sessionStorage.getItem("theme"));
                        $(".btn-theme").toggleClass("dark light");
                        $(".btn-theme").toggleClass("fa-sun text-dark fa-moon text-light");
                        SELF.newAlert("O tema escuro está ativado!", "warning");
                    }
                }, 3000);
                $(".btn-theme").click(function() {
                    $(this).toggleClass("dark light");
                    $(this).toggleClass("fa-sun text-dark fa-moon text-light");
                    if ($(this).hasClass("dark")) {
                        SELF.setTheme("dark");
                        sessionStorage.setItem("theme", "dark");
                        SELF.newAlert("O tema escuro ativado!", "success");
                    } else {
                        SELF.setTheme("light");
                        sessionStorage.setItem("theme", "light");
                        SELF.newAlert("O tema claro ativado!", "success");
                    }
                });
            }
        }).mount('#app')
    </script>
</body>

</html>