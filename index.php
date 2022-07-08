<?php
include('file.php')
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Localhost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
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
                                    <span>Renzo Nogueira & <a href="https://github.com/jhmartins1">João Heitor</a></span>
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
    <script src="script.js"></script>
</body>

</html>