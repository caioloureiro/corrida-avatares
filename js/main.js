// ===== GERENCIAMENTO DE CRONÔMETROS =====

const cronometros = {};
const TEMPO_MAXIMO = 20 * 60; // 20 minutos em segundos

function inicializarCronometro(nome) {
	if (!cronometros[nome]) {
		cronometros[nome] = {
			tempo: 0,
			ativo: false,
			intervalo: null,
		};
	}
}

function formatarTempo(segundos) {
	const horas = Math.floor(segundos / 3600);
	const minutos = Math.floor((segundos % 3600) / 60);
	const secs = segundos % 60;

	return `${String(horas).padStart(2, "0")}:${String(minutos).padStart(2, "0")}:${String(secs).padStart(2, "0")}`;
}

function startCronometro(nome) {
	inicializarCronometro(nome);
	const crono = cronometros[nome];

	// Se já está ativo, não fazer nada
	if (crono.ativo) return;

	crono.ativo = true;
	const elementoCronometro = document.getElementById(`cronometro-${nome}`);
	elementoCronometro.classList.add("ativo");

	// Desabilitar botão de start
	const btnStart = elementoCronometro.querySelector(".btn-start");
	btnStart.disabled = true;

	crono.intervalo = setInterval(() => {
		crono.tempo++;

		// Atualizar display
		const spanTempo = elementoCronometro.querySelector(".tempo");
		spanTempo.textContent = formatarTempo(crono.tempo);

		// Verificar se atingiu 20 minutos
		if (crono.tempo >= TEMPO_MAXIMO) {
			stopCronometro(nome);
			elementoCronometro.classList.add("completo");

			// Alert para próximo avatar
			const proximoNome = obterProximoAvatar(nome);
			if (proximoNome) {
				alert(
					`⏱️ ${nome} completou 20 minutos!\n\n▶️ Iniciar agora: ${proximoNome}`,
				);
			} else {
				alert(
					`🎉 ${nome} completou 20 minutos!\n\n✅ Todos os avatares foram completados!`,
				);
			}
		}
	}, 1000);
}

function stopCronometro(nome) {
	inicializarCronometro(nome);
	const crono = cronometros[nome];

	if (!crono.ativo) return;

	crono.ativo = false;
	clearInterval(crono.intervalo);

	const elementoCronometro = document.getElementById(`cronometro-${nome}`);
	elementoCronometro.classList.remove("ativo");

	// Habilitar botão de start novamente
	const btnStart = elementoCronometro.querySelector(".btn-start");
	btnStart.disabled = false;
}

function resetCronometro(nome) {
	inicializarCronometro(nome);
	const crono = cronometros[nome];

	// Parar o cronômetro se estiver rodando
	if (crono.ativo) {
		stopCronometro(nome);
	}

	// Resetar tempo
	crono.tempo = 0;

	const elementoCronometro = document.getElementById(`cronometro-${nome}`);
	const spanTempo = elementoCronometro.querySelector(".tempo");
	spanTempo.textContent = formatarTempo(0);
	elementoCronometro.classList.remove("completo");

	// Habilitar botão de start
	const btnStart = elementoCronometro.querySelector(".btn-start");
	btnStart.disabled = false;
}

function obterProximoAvatar(nomeAtual) {
	const linhas = document.querySelectorAll("table tbody tr[data-nome]");
	let encontrouAtual = false;

	for (let linha of linhas) {
		if (encontrouAtual) {
			return linha.getAttribute("data-nome");
		}
		if (linha.getAttribute("data-nome") === nomeAtual) {
			encontrouAtual = true;
		}
	}

	return null;
}

function gravarSeguidores(botao) {
	const row = botao.closest("tr");
	const input = row.querySelector(".seguidores-input").value;
	const seguidores = parseInt(input, 10);
	const nome = row.attributes["data-nome"].value;

	// Validação simples
	if (isNaN(seguidores) || seguidores < 0) {
		alert("Por favor, insira um número válido e maior ou igual a 0");
		return;
	}

	// Desabilitar botão e mostrar loading
	botao.disabled = true;
	const loading = row.querySelector(".loading");
	if (loading) loading.classList.add("active");

	var formData = new FormData();
	formData.append("seguidores", seguidores);
	formData.append("nome", nome);

	var xhr = new XMLHttpRequest();
	xhr.open("POST", "api/update-seguidores.php", true);

	xhr.onreadystatechange = function () {
		if (xhr.status === 200 && xhr.readyState == 4) {
			console.log(xhr.responseText);
			window.location.reload();
		}
	};

	xhr.send(formData);
}
