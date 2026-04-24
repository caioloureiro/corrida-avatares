// Cores para cada avatar
const cores = {
	Ana: "#6366f1",
	Bia: "#8b5cf6",
	Megg: "#ec4899",
	Luna: "#f59e0b",
	Mel: "#10b981",
};

// Configurar o gráfico
const ctx = document.getElementById("graficoEvolucao");

if (
	ctx &&
	dadosGrafico &&
	dadosGrafico.datas &&
	dadosGrafico.datas.length > 0
) {
	// Preparar dados para o Chart.js
	const datasets = [];

	for (const [avatar, dados] of Object.entries(dadosGrafico.avatares)) {
		const cores_avatar = cores[avatar] || "#6366f1";
		const dados_por_data = {};

		// Inicializar com null para todas as datas
		dadosGrafico.datas.forEach((data) => {
			dados_por_data[data] = null;
		});

		// Preencher com dados disponíveis
		dados.forEach((d) => {
			dados_por_data[d.data] = d.seguidores;
		});

		// Criar array de seguidores na ordem das datas
		const seguidores = dadosGrafico.datas.map((data) => dados_por_data[data]);

		datasets.push({
			label: avatar,
			data: seguidores,
			borderColor: cores_avatar,
			backgroundColor: cores_avatar + "20",
			borderWidth: 3,
			fill: true,
			tension: 0.4,
			pointRadius: 6,
			pointBackgroundColor: cores_avatar,
			pointBorderColor: "#fff",
			pointBorderWidth: 2,
			pointHoverRadius: 8,
			spanGaps: true,
		});
	}

	// Formatar datas para exibição
	const datasFormatadas = dadosGrafico.datas.map((data) => {
		const d = new Date(data + "T00:00:00");
		return d.toLocaleDateString("pt-BR", { day: "2-digit", month: "2-digit" });
	});

	const chart = new Chart(ctx, {
		type: "line",
		data: {
			labels: datasFormatadas,
			datasets: datasets,
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: {
					display: true,
					position: "top",
					labels: {
						color: "#cbd5e1",
						font: {
							size: 12,
							weight: "600",
						},
						padding: 20,
						usePointStyle: true,
						pointStyle: "circle",
					},
				},
				tooltip: {
					backgroundColor: "#1e293b",
					borderColor: "#6366f1",
					borderWidth: 2,
					titleColor: "#f1f5f9",
					bodyColor: "#cbd5e1",
					padding: 12,
					displayColors: true,
					callbacks: {
						label: function (context) {
							return (
								context.dataset.label + ": " + (context.parsed.y || 0) + " seguidores"
							);
						},
					},
				},
			},
			scales: {
				y: {
					beginAtZero: true,
					max: 2000,
					grid: {
						color: "#334155",
						drawBorder: true,
					},
					ticks: {
						color: "#cbd5e1",
						font: {
							size: 11,
						},
						callback: function (value) {
							return value.toLocaleString("pt-BR");
						},
					},
					title: {
						display: true,
						text: "Seguidores",
						color: "#cbd5e1",
						font: {
							size: 12,
							weight: "600",
						},
					},
				},
				x: {
					grid: {
						display: false,
					},
					ticks: {
						color: "#cbd5e1",
						font: {
							size: 11,
						},
					},
					title: {
						display: true,
						text: "Data",
						color: "#cbd5e1",
						font: {
							size: 12,
							weight: "600",
						},
					},
				},
			},
		},
	});
}
