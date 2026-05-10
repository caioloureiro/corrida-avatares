function gravarSeguidores(botao) {
	const row = botao.closest("tr");
	const input = row.querySelector(".seguidores-input");
	const seguidores = parseInt(input.value, 10);

	// Validação simples
	if (isNaN(seguidores) || seguidores < 0) {
		alert("Por favor, insira um número válido e maior que 0");
		return;
	}

	// Desabilitar botão e mostrar loading
	botao.disabled = true;
	const loading = row.querySelector(".loading");
	if (loading) loading.classList.add("active");

	// Enviar dados
	fetch("./api/update-seguidores.php", {
		method: "POST",
		headers: { "Content-Type": "application/json" },
		body: JSON.stringify({
			id: row.dataset.id,
			nome: row.dataset.nome,
			seguidores: seguidores,
		}),
	})
		.then((response) => response.json())
		.then((data) => {
			if (data.success) {
				// Feedback visual de sucesso
				input.style.backgroundColor = "rgba(16, 185, 129, 0.2)";
				setTimeout(() => {
					input.style.backgroundColor = "rgba(99, 102, 241, 0.1)";
					location.reload();
				}, 800);
			} else {
				alert("Erro ao atualizar: " + data.message);
			}
		})
		.catch((error) => {
			alert("Erro ao conectar com o servidor: " + error.message);
		})
		.finally(() => {
			botao.disabled = false;
			if (loading) loading.classList.remove("active");
		});
}
