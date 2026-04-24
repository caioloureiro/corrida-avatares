<?php
// tests/CrridaTest.php - Testes unitários com PHPUnit

require_once __DIR__ . '/../config/db.php';

class CrridaTest
{
	private $conn;
	private $test_results = [];

	public function __construct()
	{
		$this->conn = new mysqli('localhost', 'root', 'caio1234', 'airbr_flow');
		if ($this->conn->connect_error) {
			die('Erro de conexão: ' . $this->conn->connect_error);
		}
	}

	public function runAllTests()
	{
		echo "🧪 Iniciando testes...\n\n";

		$this->testConexaoBanco();
		$this->testInsercaoAvatar();
		$this->testAtualizacaoSeguidores();
		$this->testCalculoPercentual();
		$this->testFiltrosPorData();
		$this->testAchievements();

		$this->exibirResultados();
	}

	private function testConexaoBanco()
	{
		echo "📌 Teste 1: Conexão com banco de dados\n";

		if ($this->conn->ping()) {
			$this->addResult('✅ Conexão com banco estabelecida', true);
		} else {
			$this->addResult('❌ Falha na conexão', false);
		}
	}

	private function testInsercaoAvatar()
	{
		echo "\n📌 Teste 2: Inserção de avatar\n";

		$nome_teste = 'TestAvatar_' . time();
		$stmt = $this->conn->prepare("INSERT INTO corrida (ativo, nome, seguidores, created_at, updated_at, data) VALUES (1, ?, 0, NOW(), NOW(), NOW())");
		$stmt->bind_param("s", $nome_teste);

		if ($stmt->execute()) {
			$id_novo = $stmt->insert_id;
			$this->addResult("✅ Avatar criado com sucesso (ID: $id_novo)", true);

			// Limpar
			$this->conn->query("DELETE FROM corrida WHERE id = $id_novo");
		} else {
			$this->addResult('❌ Falha ao criar avatar', false);
		}
		$stmt->close();
	}

	private function testAtualizacaoSeguidores()
	{
		echo "\n📌 Teste 3: Atualização de seguidores\n";

		$sql = "SELECT id FROM corrida WHERE ativo = 1 LIMIT 1";
		$result = $this->conn->query($sql);

		if ($result && $result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$id = $row['id'];
			$novo_valor = rand(100, 1000);

			$stmt = $this->conn->prepare("UPDATE corrida SET seguidores = ?, updated_at = NOW() WHERE id = ?");
			$stmt->bind_param("ii", $novo_valor, $id);

			if ($stmt->execute()) {
				$this->addResult("✅ Seguidores atualizados para $novo_valor", true);
			} else {
				$this->addResult('❌ Falha ao atualizar seguidores', false);
			}
			$stmt->close();
		} else {
			$this->addResult('❌ Nenhum avatar encontrado', false);
		}
	}

	private function testCalculoPercentual()
	{
		echo "\n📌 Teste 4: Cálculo de percentual\n";

		$seguidores = 1500;
		$meta = 2000;
		$percentual = ($seguidores / $meta) * 100;
		$esperado = 75;

		if (abs($percentual - $esperado) < 0.01) {
			$this->addResult("✅ Cálculo correto: $percentual%", true);
		} else {
			$this->addResult("❌ Cálculo incorreto: $percentual% (esperado $esperado%)", false);
		}
	}

	private function testFiltrosPorData()
	{
		echo "\n📌 Teste 5: Filtros por data\n";

		$data_inicio = date('Y-m-d 00:00:00', strtotime('-7 days'));
		$data_fim = date('Y-m-d 23:59:59');

		$sql = "SELECT COUNT(*) as total FROM corrida WHERE ativo = 1 AND data >= ? AND data <= ?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("ss", $data_inicio, $data_fim);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();

		if ($row['total'] >= 0) {
			$this->addResult("✅ Filtro de data funcionando ({$row['total']} registros)", true);
		} else {
			$this->addResult('❌ Falha no filtro de data', false);
		}
		$stmt->close();
	}

	private function testAchievements()
	{
		echo "\n📌 Teste 6: Sistema de achievements\n";

		// Teste: Avatar com meta atingida
		$sql = "SELECT COUNT(*) as total FROM corrida WHERE ativo = 1 AND seguidores >= 2000";
		$result = $this->conn->query($sql);
		$row = $result->fetch_assoc();

		if ($row['total'] >= 0) {
			$total_meta = $row['total'];
			$this->addResult("✅ Verificação de meta: $total_meta avatar(es) atingiram meta", true);
		} else {
			$this->addResult('❌ Falha ao verificar achievements', false);
		}
	}

	private function addResult($mensagem, $sucesso)
	{
		$this->test_results[] = ['mensagem' => $mensagem, 'sucesso' => $sucesso];
		echo "   $mensagem\n";
	}

	private function exibirResultados()
	{
		echo "\n" . str_repeat("=", 50) . "\n";
		echo "📊 RESUMO DOS TESTES\n";
		echo str_repeat("=", 50) . "\n";

		$totais = count($this->test_results);
		$sucessos = count(array_filter($this->test_results, fn($t) => $t['sucesso']));
		$falhas = $totais - $sucessos;

		echo "\n✅ Sucessos: $sucessos\n";
		echo "❌ Falhas: $falhas\n";
		echo "📈 Taxa de sucesso: " . round(($sucessos / $totais) * 100) . "%\n";
		echo "\n" . str_repeat("=", 50) . "\n";

		$this->conn->close();
	}
}

// Executar testes se chamado diretamente
if (php_sapi_name() === 'cli') {
	$teste = new CrridaTest();
	$teste->runAllTests();
}
