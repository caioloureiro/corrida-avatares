document.addEventListener("DOMContentLoaded", function () {
	const table = document.querySelector("tbody");

	if (table) {
		table.addEventListener("keydown", handleInputKeydown);
		table.addEventListener("blur", handleInputBlur, true);
	}
});

function handleInputKeydown(e) {
	if (e.key === "Enter") {
		const input = e.target;
		if (input.classList.contains("seguidores-input")) {
			e.preventDefault();
			updateSeguidores(input);
		}
	}
}

function handleInputBlur(e) {
	if (e.target.classList.contains("seguidores-input")) {
		// Validar quando o input perde o foco também
		const row = e.target.closest("tr");
		if (row) {
			const originalValue = row.dataset.originalValue;
			const currentValue = e.target.value;
			if (originalValue !== currentValue) {
				updateSeguidores(e.target);
			}
		}
	}
}

function updateSeguidores(input) {
	const row = input.closest("tr");
	const id = row.dataset.id;
	const seguidores = parseInt(input.value, 10);

	// Validar se é um número válido
	if (isNaN(seguidores) || seguidores < 0) {
		alert("Por favor, insira um número válido e maior que 0");
		input.value = row.dataset.originalValue;
		return;
	}

	// Mostrar indicador de carregamento
	const loading = row.querySelector(".loading");
	if (loading) {
		loading.classList.add("active");
	}

	// Desabilitar o input enquanto atualiza
	input.disabled = true;

	fetch("/api/update-seguidores.php", {
		method: "POST",
		headers: {
			"Content-Type": "application/json",
		},
		body: JSON.stringify({
			id: id,
			seguidores: seguidores,
		}),
	})
		.then((response) => {
			if (!response.ok) {
				throw new Error(`Erro: ${response.status}`);
			}
			return response.json();
		})
		.then((data) => {
			if (data.success) {
				// Atualizar o valor original
				row.dataset.originalValue = seguidores;

				// Atualizar a barra de progresso
				const percentage = (seguidores / 2000) * 100;
				const progressBar = row.querySelector(".progress-bar");
				if (progressBar) {
					progressBar.style.width = Math.min(percentage, 100) + "%";
				}

				const percentageText = row.querySelector(".percentage");
				if (percentageText) {
					percentageText.textContent = percentage.toFixed(2) + "%";
				}

				// Adicionar feedback visual
				input.style.backgroundColor = "rgba(16, 185, 129, 0.2)";
				setTimeout(() => {
					input.style.backgroundColor = "rgba(99, 102, 241, 0.1)";
				}, 1000);

				// Recarregar a página para atualizar rankings
				setTimeout(() => {
					location.reload();
				}, 800);
			} else {
				alert("Erro ao atualizar: " + data.message);
				input.value = row.dataset.originalValue;
			}
		})
		.catch((error) => {
			console.error("Erro:", error);
			alert("Erro ao conectar com o servidor: " + error.message);
			input.value = row.dataset.originalValue;
		})
		.finally(() => {
			// Remover indicador de carregamento
			if (loading) {
				loading.classList.remove("active");
			}

			// Habilitar o input novamente
			input.disabled = false;
		});
}
