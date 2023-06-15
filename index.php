<?php

/*
TODO
- Adicionar configuração de interesses para o tema do plano de fundo
- Adicionar botão para desabilitar o plano de fundo
- Terminar página de gerenciamento do banco de dados.
*/

$keys = json_decode(file_get_contents('.env'), true);
define("KEY_OPENAI", $keys["KEY_OPENAI"]);
define("KEY_PEXELS", $keys["KEY_PEXELS"]);

// Se não existir o arquivo favorites.json irá cria-lo
$favorites = file_exists('favorites.json') ? json_decode(file_get_contents('favorites.json'), true) : createFavorites();

function createFavorites()
{
	$favorites = [];
	file_put_contents('favorites.json', json_encode($favorites));
}

// Função para o chatbot
function chatbot($messages, $treinamento)
{
	// global KEY_OPENAI;
	// Requisição para a API da Open AI gpt-3.5-turbo
	$curl = curl_init();
	$headers = array(
		"Content-Type: application/json",
		"Authorization: Bearer " . KEY_OPENAI
	);
	// Adiconar treinamento na primeira posição do array messages
	$treinamento = [
		"role" => "system",
		"content" => $treinamento
	];
	array_unshift($messages, $treinamento);
	curl_setopt_array($curl, array(
		CURLOPT_URL => "https://api.openai.com/v1/chat/completions",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => json_encode(array(
			"model" => "gpt-3.5-turbo",
			"messages" =>  $messages,
			"max_tokens" => 300,
			"temperature" => 0.7,
		)),
		CURLOPT_HTTPHEADER => $headers,
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);

	if ($err) {
		return "cURL Error #:" . $err;
	} else {
		$response = json_decode($response, true);
		$response = $response["choices"][0]["message"];
		return $response;
	}
}

if (isset($_POST["addFavorite"])) {
	define("NEW_FAVORITE", json_decode($_POST["addFavorite"]));
	if (!in_array(NEW_FAVORITE, $favorites)) { // Verifica se o favorito já não existe no array
		array_push($favorites, json_decode($_POST["addFavorite"], true));
		file_put_contents('favorites.json', json_encode($favorites)); // Salva o arquivo
	}
	die();
}

if (isset($_POST["removeFavorite"])) {
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
}

if (isset($_POST["directory"])) {
	// Lê os arquivos e pastas da pasta atual e pega os seus tipos
	$directory = json_decode($_POST["directory"]);
	$data = scandir($directory);
	$data = array_diff($data, ['.', '..']);
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
}

if (isset($_POST["getFavorites"])) {
	echo json_encode($favorites);
	die();
}

if (isset($_POST["messages"])) {
	$messages = json_decode($_POST["messages"]);
	$treinamento = "Meu objetivo como bot auxiliar e fornecer suporte e orientação para programadores em tarefas básicas do cotidiano. Posso ajudar na escolha de uma linguagem de programação, encontrar tutoriais ou sugerir abordagens para resolver problemas específicos de programação. Por favor, me forneça uma tarefa específica para que eu possa ajudá-lo.";
	echo json_encode(chatbot($messages, $treinamento));
	die();
}

if (isset($_POST["getUser"])) {
	// pega o nome do usuário logado na máquina
	define("USER", getenv('USERNAME'));
	echo json_encode(USER);
	die();
}

if (isset($_POST["historic"])) {
	// Salva o histórico de conversa nos cookies
	$historic = json_decode($_POST["historic"]);
	// Se for maior que 5, remove o primeiro item do array
	while (count($historic) > 5) {
		array_shift($historic);
	}
	// Uma semana
	setcookie("historic", json_encode($historic), time() + 60 * 60 * 24 * 7);
	die();
}

if (isset($_POST["getHistoric"])) {
	// Pega o histórico de conversa nos cookies
	$historic = json_decode($_COOKIE["historic"]);
	// Verifica se o histórico existe
	if ($historic) {
		echo json_encode($historic);
	} else {
		echo json_encode([]);
	}
	die();
}

// Pega uma busca aleatória diária com o chatbot
$day = date("d");
// Compara com o dia anterior salvo nos cookies
$requestDataVideo = [];
if (isset($_COOKIE["day"]) && isset($_COOKIE["requestVideo"]) && $_COOKIE["day"] == $day) {
	// Pega a busca salva nos cookies
	$request = json_decode($_COOKIE["requestVideo"]);
	$requestVideo = $request[0];
	$requestKeyword = $request[1];
	$requestDataVideo = changeVideoBackground($requestKeyword);
} else {
	$requestDataVideo = changeVideoBackground();
}

if (isset($_POST["changeVideoBackground"])) {
	$requestDataVideo = changeVideoBackground();
	echo json_encode($requestDataVideo);
	die();
}

function changeVideoBackground($keyVideo = "")
{
	if ($keyVideo == "") {

		$videoThemes = array(
			"technology",
			"fashion",
			"travel",
			"food",
			"fitness",
			"beauty",
			"diy",
			"gaming",
			"music",
			"art",
			"education",
			"entertainment",
			"news",
			"sports",
			"business",
			"finance",
			"politics",
			"science",
			"health",
			"lifestyle",
			"culture",
			// "humor",
			// "spirituality",
			"environment",
			"history",
			"social media",
			"interviews",
			// "tutorials",
			// "comedy",
			"animation"
		);

		$keyVideo = $videoThemes[rand(0, count($videoThemes) - 1)];
	}
	// Vídeo background
	$ch = curl_init();
	// Cabeçalho da requisição
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: ' . KEY_PEXELS
	));
	$count = 20;
	curl_setopt($ch, CURLOPT_URL, "https://api.pexels.com/videos/search?query={$keyVideo}&orientation=landscape&size=medium&per_page={$count}");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	$json = json_decode($result, true);
	$videos = $json['videos'];
	// Randomiza o vídeo a posição do array
	$p = rand(0, count($videos) - 1);
	$requestVideo = $videos[$p]['video_files'][0]['link'];
	// Salva a busca nos cookies durante um dia
	setcookie("requestVideo", json_encode([$requestVideo, $keyVideo]), time() + 60 * 60 * 24 * 7);
	setcookie("day", date("d"), time() + 60 * 60 * 24 * 7);
	return [
		"requestVideo" => $requestVideo,
		"requestKeyword" => $keyVideo
	];
}

?>
<!DOCTYPE html>
<html lang="pt-br">

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

		a button,
		textarea,
		p,
		h1,
		h2,
		h3,
		h4,
		h5,
		h6,
		span:not(#historic a) {
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

		.item-grid .card-body {
			background-color: transparent;
		}

		.item-grid .card-body a {
			color: black !important;
		}

		.dark .item-grid:hover {
			background-color: var(--bs-danger) !important;
		}

		.dark #historic a {
			color: white !important;
		}

		.light #historic {
			color: var(--bs-secondary) !important;
		}

		.light .item-grid:hover {
			background-color: var(--bs-secondary) !important;
		}

		.item-grid:hover a,
		.item-grid:hover span,
		.item-grid:hover i:not(.fa-folder),
		.favorite:hover a {
			color: white !important;
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

		/* Vídeo background */
		video {
			position: fixed;
			right: 0;
			bottom: 0;
			min-width: 100%;
			min-height: 100%;
			z-index: -1;
			object-fit: cover;
		}

		#btn-change-background {
			z-index: 999 !important;
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

		.btn-layout {
			position: relative;
			animation: btn-layout-animation 1s ease-in-out !important;
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

		/* btn-layout surge subindo em efeito de zoom */
		@keyframes btn-layout-animation {
			0% {
				opacity: 0;
				transform: scale(0.5);
				transform: translateY(30%);
			}

			100% {
				opacity: 1;
				transform: scale(1);
				transform: translateY(0);
			}
		}

		.btn-theme:hover {
			filter: brightness(0.9);
			transform: scale(1.009);
		}

		.theme-background-keyword {
			background-color: var(--background_4) !important;
		}
	</style>
</head>

<body class="position-relative">
	<main id="app" class="py-4">
		<!-- Fim NavBar menu hamburguer -->
		<nav class="navbar navbar-light">
			<div class="container-fluid">
				<div class="theme-background-keyword border border-light rounded-3 px-2">
					<!-- <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button> -->
					<span class="navbar-brand mb-0 ms-4 h4">{{ keyWordVideo}}</span>
				</div>
				<!-- <div class="collapse navbar-collapse ps-2 mt-2" id="navbarNav">
					<ul class="navbar-nav">
						<li class="nav-item">
							<a id="link-list-files" class="nav-link active" aria-current="page" href="#" @click="togglePanel('list-files')">Arquivos</a>
						</li>
						<li class="nav-item">
							<a id="link-data-base" class="nav-link" href="#" @click="togglePanel('data-base')">Banco de dados</a>
						</li>
					</ul>
				</div> -->
			</div>
		</nav>
		<!-- Fim NavBar menu hamburguer -->

		<!-- Botão para trocar o plano de fundo -->
		<div class="position-fixed top-0 end-0 m-3">
			<div>
				<button class="btn btn-sm border" id="btn-change-background" @click="changeBackground">
					<i class="fas fa-sync-alt text-white"></i>
				</button>
			</div>
		</div>

		<div class="container position-relative list-files mt-4" :class="{'light': themeAplycated === 'light', 'dark': themeAplycated === 'dark'}">

			<!-- Painel de arquivos -->
			<div class="row panel" id="list-files">
				<div class="col-md-8">
					<div class="card">
						<div class="card-header d-flex justify-content-between align-items-center">
							<h3>Lista de arquivos e pastas</h3>
							<div>
								<!-- Ícone de sol  -->
								<i class="fas fa-sun fa-2x fs-5 float-right text-dark btn-theme light mx-4" role="button" data-bs-toggle="tooltip" data-bs-placement="left" title="Alternar tema"></i>
								<!-- Ícone para mudar o layout  da lista  -->
								<i class="fas fa-list fa-2x fs-5 float-right text-dark btn-layout light grid" role="button" data-bs-toggle="tooltip" data-bs-placement="left" title="Mudar para grade"></i>
							</div>
						</div>
						<div class="card-body">
							<!-- Lista horizontal de favoritos -->
							<div class="row">
								<div class="col-md-12">
									<h4>Favoritos</h4>
									<button v-for="favorite in favorites" class="btn favorite btn-outline-secondary m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" :title="favorite">
										<a :href="favorite" target="_blank" class="text-decoration-none text-dark m-2">{{favorite.substring(0, 6)}}...</a>
										<!-- Remover -->
										<a href="#" @click="removeFavorite(favorite)" class="text-decoration-none text-dark"><i class="fas fa-times"></i>
									</button>
								</div>
							</div>

							<div>
								<!-- Lista em Navtabs os diretórios do histórico -->
								<ul class="nav nav-tabs" id="historic" role="tablist">
									<li v-for="history in historic" class="nav-item d-flex align-items-center">
										<a :href="history.name" class="nav-link text-secondary" :id="history.name" data-toggle="tab" role="tab" aria-controls="home" aria-selected="true" target="_blank"><i :class="history.type"></i> {{ history.name }}</a>
									</li>
								</ul>
								<!-- Layout de lista -->
								<ul v-if="themes.layout.selected" class="list-group">
									<!-- Loading com v-show enquanto files estiver vazio -->
									<li class="list-group-item text-center" v-show="files.length === 0">
										<div class="spinner-border text-secondary" role="status"></div>
									</li>
									<li v-for="(file, index) in files" class="list-group-item element-item border-0">
										<!-- Verifica de o index é par ou impar -->
										<div :class="['row','file-item', index % 2 === 0 ? 'bg-light' : 'bg-white']">
											<div class="col-md-12 d-flex justify-content-between px-3">
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
								<!-- Layout de grade -->
								<div v-else class="row pt-2">
									<div v-for="file in files" class="col-md-4">
										<div class="card item-grid m-1">
											<div class="card-body row">
												<div class="col-10">
													<h5 class="card-title">
														<i :class="[file.type, (file.type.includes('fa-folder') ? 'text-warning': 'text-secondary')]"></i>
														<a class="ms-2 text-decoration-none" @click="setHistory({name:file.name, type:file.type})" :href="file.name" :title="file.name" class="text-decoration-none text-dark" target="_blank">{{ file.name
														}}</a>
													</h5>
												</div>
												<div class="col-2">
													<button v-show="!favorites.includes(file.name)" @click="addFavorite(file)" class="btn btn-link p-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Adicionar aos favoritos">
														<i class="fas fa-star text-secondary"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-4">
					<!-- Campo de pesquisa dos arquivos -->
					<div class="card">
						<div class="card-header d-flex justify-content-between align-items-center">
							<h3>Pesquisar na lista</h3>
						</div>
						<div class="card-body">
							<div class="input-group mb-3">
								<input id="search" type="text" class="form-control" placeholder="Pesquisar..." aria-label="Pesquisar..." aria-describedby="button-addon2" v-model="search">
								<button class="btn btn-outline-secondary" type="button" id="button-addon2" @click="searchFiles(search)"><i class="fas fa-search"></i></button>
							</div>
						</div>
					</div>
					<div class="card mt-2">
						<div class="card-header d-flex justify-content-between align-items-center">
							<h3>Assistente</h3>
						</div>
						<div id="chat" class="card-body position-relative text-center px-4 pb-5 d-flex flex-column justify-content-center align-items-baseline" style="max-height: 480px;">
							<div id="response" class="pb-4" style="overflow-y: auto;max-height: 480px;">
								<div v-for="message in messages" class="card my-2 w-100 border-0" :class="{ 'd-flex justify-content-end': message.role === 'user' }">
									<div class="card-body p-0" :class="{ 'justify-content-end': message.role === 'user' }">
										<div class="text-start border border-light shadow-sm rounded p-2" :class="{ 'text-end bg-primary text-light': message.role === 'user' }">
											<p class="card-text">{{ message.content }}</p>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex bg-light shadow-sm p-2 justify-content-between align-items-cente position-absolute bottom-0 start-0 end-0">
								<input id="inputUser" type="text" class="form-control" v-model="message" placeholder="Digite aqui...">
								<button type="button" class="btn btn-primary ms-2 text-light" id="btnSend" @click="sendMessage()">Enviar
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Footer com Copyright -->
			<div class="mt-2">
				<div>
					<div class="card">
						<div class="card-footer">
							<p class="text-center">
								<small> <i class="fa fa-copyright"></i> <span class="px-2">Copyright <?= date('Y') ?></span>
									<span>-</span> <span class="px-2">Desenvolvido por</span>
									<span>Renzo Nogueira</span>
								</small>
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="position-fixed" id="alerts" style="bottom: 30px; right: 20px; max-width: 600px;"></div>
		</div>

		<!-- Brackground video -->
		<video autoplay muted loop id="myVideo" id="video-background" class="video-background position-fixed w-100 h-100 top-0 left-0 m-0 p-0" preload="auto" playsinline>
			<source src="<?= $requestDataVideo["requestVideo"]; ?>" type="video/mp4">
		</video>


	</main>

	<script src="https://kit.fontawesome.com/274af9ab8f.js" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script src="https://unpkg.com/vue@3"></script>
	<script>
		Vue.createApp({
			data() {
				return {
					directory: ".",
					userName: null,
					themeAplycated: "light",
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
							background_1: '#F9F9F9',
							background_2: '#FFFFFF',
							background_3: '#E5E5E5',
							background_4: '#F2F2F2',
							text: '#333333'
						},
						dark: {
							background_1: '#181818',
							background_2: '#2f2f2f',
							background_3: '#474747',
							background_4: '#5e5e5e',
							text: '#ffffff'
						},
						layout: {
							selected: true,
							layouts: ['list', 'grid']
						}
					},
					message: "",
					messages: [{
						role: "assistant",
						content: "Olá {{userName}}, eu sou o seu assistente virtual, como posso te ajudar?"
					}],
					search: "",
					srcVideo: "<?= $requestDataVideo["requestVideo"]; ?>",
					keyWordVideo: "<?= $requestDataVideo["requestKeyword"]; ?>",
				}
			},
			// Inicialização
			beforeMount: function() {
				this.getUser()
				this.openFolder();
				this.getFavorites();
				// Chamar a função a cada 1 min
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
				},
				search: function(value) {
					if (value.length > 0) {
						this.files = this.files.filter(file => file.name.toLowerCase().includes(value.toLowerCase()));
						this.folders = this.folders.filter(folder => folder.name.toLowerCase().includes(value.toLowerCase()));
					} else {
						this.openFolder();
					}
				}
			},

			methods: {
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

				// Trocar entre as abas
				togglePanel: function(panel) {
					$(".panel").addClass("d-none");
					$(`#${panel}`).removeClass("d-none");
					$(".nav-link").removeClass("active");
					$(`#link-${panel}`).addClass("active");
					this.newAlert(`Você está na aba ${panel}`, "success");
				},

				setHistory: function(itemClicked) {
					if (this.historic.find(item => item.name === itemClicked.name)) {
						// Busca o item no array e atualiza o numero de acessos e a data
						this.historic.find(item => item.name === itemClicked.name).accesses++;
						this.historic.find(item => item.name === itemClicked.name).date.push(new Date());
					} else {
						// Adiciona o item no array
						this.historic.push({
							name: itemClicked.name,
							date: [new Date()],
							type: itemClicked.type,
							accesses: 1
						});

						// Se for maior que 5, remove o primeiro item
						if (this.historic.length > 5) {
							this.historic.shift();
						}
					}

					$.post({
						url: 'index.php',
						type: 'POST',
						data: {
							historic: JSON.stringify(this.historic)
						}
					});
				},

				getHistory: function() {
					const SELF = this;
					$.post({
						url: '#',
						type: 'POST',
						data: {
							getHistoric: true
						},
						success: function(data) {
							data = JSON.parse(data);
							// remove o item do array se tiver o name igual a "./"
							data = data.filter(item => item !== "./");
							SELF.historic = data;
						}
					});
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
									type: SELF.fileTypes["folder"]
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
											SELF.files.push({
												name: dataFiles["files"][key],
												type: SELF.fileTypes[type]
											});
											fileTypeExist = true;
										}
									}
								});
								if (!fileTypeExist) {
									SELF.files.push({
										name: dataFiles["files"][key],
										type: SELF.fileTypes["file"]
									});
								}
							});

							// Remove os arquivos favorites.json, index.php, .git, README.md, .gitignore, LICENSE, .idea, .env
							SELF.files = SELF.files.filter(item => item.name !== "favorites.json" && item.name !== "index.php" && item.name !== ".git" && item.name !== "README.md" && item.name !== ".gitignore" && item.name !== "LICENSE" && item.name !== ".idea" && item.name !== ".env");
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
							SELF.setUserNameChat(SELF.userName); // Define o nome do usuário no chat
						}
					});
				},

				// Função para definir o nome do usuário no chat
				setUserNameChat: function(name) {
					const SELF = this;
					SELF.messages[0].content = this.messages[0].content.replace("{{userName}}", name);
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
						$(`:root`).css(`--${key}`, SELF.themes[theme][key]);
					});

					SELF.themeAplycated = theme;
				},

				sendMessage: function() {
					const SELF = this;
					SELF.messages.push({
						role: "user",
						content: SELF.message
					});
					SELF.message = "";
					$("#chat #response").append(`
						<div id="loadMessage" class="card-body w-100 p-0" role="status"">
							<div class="text-start border border-light shadow-sm rounded p-2 bg-success text-light">
								<small class="text-light">Processando...</small>
							</div>
						</div>
					`);
					// Animação de pulsar do load
					$("#loadMessage").animate({
						opacity: 0.5
					}, 1000).animate({
						opacity: 1
					}, 1000);
					$("#chat #response").animate({
						scrollTop: $("#chat #response").prop("scrollHeight")
					}, 1000);
					$.post({
						url: '#',
						type: 'POST',
						data: {
							messages: JSON.stringify(SELF.messages)
						}
					}).done(function(response) {
						SELF.messages.push(JSON.parse(response));
						$("#chat #response").animate({
							scrollTop: $("#chat #response").prop("scrollHeight")
						}, 1000);
						$("#loadMessage").remove();
					});
				},

				// Muda o vídeo do background
				changeBackground: function() {
					const SELF = this;
					$.post("#", {
						changeVideoBackground: true
					}).done(function(data) {
						data = JSON.parse(data);
						SELF.srcVideo = data.requestVideo
						SELF.keyWordVideo = data.requestKeyword;
						$("video").attr("src", SELF.srcVideo);
					});
				},
			},

			mounted: function() {
				const SELF = this;
				SELF.getHistory();
				window.onload = function() {
					const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
					const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
						return new bootstrap.Tooltip(tooltipTriggerEl)
					})
				}

				$("#inputUser").on("keyup", function(e) {
					if (e.keyCode === 13) {
						SELF.sendMessage();
					}
				});

				const BTN_THEME = $(".btn-theme");
				const BTN_LAYOUT = $(".btn-layout");
				setTimeout(() => {
					this.newAlert(`Seja bem vindo ${this.userName}!`, "success")
					this.newAlert("A lista será atualizada daqui <b>1</b> minuto.", "info")
					const THEME_SAVE = localStorage.getItem("theme");
					if (THEME_SAVE && THEME_SAVE === "dark") { // Verifica se existe um tema salvo
						SELF.themeAplycated = THEME_SAVE
							SELF.setTheme(SELF.themeAplycated);
							BTN_THEME.toggleClass("dark light");
							BTN_THEME.toggleClass("fa-sun text-dark fa-moon text-light");
							BTN_LAYOUT.toggleClass("dark light");
							BTN_LAYOUT.toggleClass("text-dark text-light");
							SELF.newAlert("O tema escuro está ativado!", "warning");
					} else {
						SELF.themeAplycated = "light";
						SELF.setTheme("light");
					}

					if (localStorage.getItem("layout") === "list") {
						SELF.themes.layout.selected = true;
						BTN_LAYOUT.toggleClass("list grid");
						BTN_LAYOUT.toggleClass("fa-th-large fa-list");
						SELF.newAlert("O layout de lista está ativado!", "warning");
					} else {
						SELF.themes.layout.selected = false;
					}
				}, 1000);

				BTN_THEME.click(function() { // Altera o tema
					$(this).toggleClass("dark light");
					$(this).toggleClass("fa-sun text-dark fa-moon text-light");
					BTN_LAYOUT.toggleClass("dark light");
					BTN_LAYOUT.toggleClass("text-dark text-light");
					if ($(this).hasClass("dark")) {
						SELF.setTheme("dark");
						localStorage.setItem("theme", "dark");
						SELF.newAlert("Tema escuro ativado!", "success");
					} else {
						SELF.setTheme("light");
						localStorage.setItem("theme", "light");
						SELF.newAlert("Tema claro ativado!", "success");
					}
				});

				BTN_LAYOUT.click(function() { // Altera o layout
					$(this).toggleClass("list grid");
					$(this).toggleClass("fa-list fa-th-large");
					if ($(this).hasClass("list")) {
						SELF.layout = "list";
						localStorage.setItem("layout", "list");
						SELF.newAlert("O layout de lista ativado!", "success");
					} else {
						SELF.layout = "grid";
						localStorage.setItem("layout", "grid");
						SELF.newAlert("O layout de grade ativado!", "success");
					}
					SELF.themes.layout.selected = !SELF.themes.layout.selected; // Atualiza o layout

					// Recarrega o layout do vue
					SELF.$forceUpdate();
				});
			}
		}).mount('#app')
	</script>
</body>

</html>