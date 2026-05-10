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
