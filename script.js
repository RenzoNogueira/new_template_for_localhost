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
                        ".gitignore": "fab fa-git",
                        ".md": "fas fa-file-alt",
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