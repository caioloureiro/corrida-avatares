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

console.log("Dados do gráfico recebidos:", dadosGrafico);

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

		// Criar array de seguidores na ordem das datas
		// Os dados já vêm na ordem correta, basta extrair os seguidores
		const seguidores = dados.map((d) => d.seguidores);

		console.log(`Avatar "${avatar}":`, dados);

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

	// Formatar datas para exibição (mantendo ordem cronológica)
	const datasFormatadas = dadosGrafico.datas.map((data) => {
		// data vem em formato YYYY-MM-DD
		const [ano, mes, dia] = data.split("-");
		return `${dia}/${mes}`;
	});

	console.log("Datas formatadas:", datasFormatadas);
	console.log("Datasets:", datasets);

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
						maxRotation: 45,
						minRotation: 0,
						// Mostrar apenas alguns rótulos se houver muitos dias
						callback: function (value, index, values) {
							if (values.length > 14) {
								// Se mais de 14 dias, mostrar a cada 2 dias
								return index % 2 === 0 ? this.getLabelForValue(value) : "";
							}
							return this.getLabelForValue(value);
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
