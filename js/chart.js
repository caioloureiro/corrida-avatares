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
	const datas = dadosGrafico.datas; // Datas únicas

	// Formatar datas para exibição
	const datasFormatadas = datas.map((data) => {
		const [ano, mes, dia] = data.split("-");
		return `${dia}/${mes}/${ano}`;
	});

	for (const [avatar, dados] of Object.entries(dadosGrafico.avatares)) {
		const cores_avatar = cores[avatar] || "#6366f1";

		// Criar um array com todos os pontos (mesmo tamanho de datas)
		// Preencher apenas com os valores onde o avatar aparece naquela data
		const valores = new Array(datas.length).fill(null);

		dados.forEach((registro) => {
			const dataIndex = datas.indexOf(registro.data);
			if (dataIndex !== -1) {
				// Se já existe um valor para esta data, usar o último
				valores[dataIndex] = registro.seguidores;
			}
		});

		datasets.push({
			label: avatar,
			data: valores,
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

	console.log("Datas:", datas);
	console.log("Datas Formatadas:", datasFormatadas);
	console.log("Datasets:", datasets);

	// Encontrar o valor máximo de seguidores
	let maxSeguidores = 0;
	datasets.forEach((dataset) => {
		dataset.data.forEach((valor) => {
			if (valor !== null && valor > maxSeguidores) {
				maxSeguidores = valor;
			}
		});
	});

	// Usar o máximo como teto (sem margem adicional para bater no topo)
	const maxComMargem = maxSeguidores > 0 ? Math.ceil(maxSeguidores) : 100;

	const chart = new Chart(ctx, {
		type: "line",
		data: {
			labels: datasFormatadas,
			datasets: datasets,
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			clip: false,
			layout: {
				padding: {
					bottom: 0,
					top: 0,
					left: 10,
					right: 10,
				},
			},
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
						title: function (context) {
							return context[0].label;
						},
					},
				},
			},
			scales: {
				xAxes: [
					{
						display: false,
					},
				],
				yAxes: [
					{
						display: false,
					},
				],
			},
		},
	});
}
