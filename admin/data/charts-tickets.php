<?php
session_start();
require_once '../dbconnection.php';
include '../checklogin.php';
check_login("admin");

$user_id = $_SESSION['user_id'] ?? 0;

//Tickets Abiertos
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket WHERE status = 'Abierto' AND assigned_to = ?");
$stmt->execute([$user_id]);
$ticketsAbiertos = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Estadísticas de Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="../../styles/admin/charts-tickets.css" rel="stylesheet" />
</head>

<body class="bg-light">
    <?php include("../header.php"); ?>

    <div class="container-fluid py-5 mt-5">
        <div class="container">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2 fw-bold text-primary">
                        <i class="bi bi-bar-chart-line me-2"></i>Estadísticas de Tickets
                    </h1>
                    <a href="../home.php" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Dashboard
                    </a>
                </div>
            </div>

            <!-- Metrics Row -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card metric-card border-start border-primary h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bi bi-exclamation-octagon-fill text-primary fs-1"></i>
                            </div>
                            <h2 class="display-4 fw-bold text-primary mb-2" id="openTicketsCount">0</h2>
                            <p class="text-muted mb-0 fw-medium">Tickets Abiertos</p>
                            <small class="text-muted">Pendientes de resolver</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card metric-card border-start border-success h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bi bi-check-circle-fill text-success fs-1"></i>
                            </div>
                            <h2 class="display-4 fw-bold text-success mb-2" id="closedTicketsCount">0</h2>
                            <p class="text-muted mb-0 fw-medium">Tickets Resueltos</p>
                            <small class="text-muted">En el último mes</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card metric-card border-start border-info h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bi bi-people-fill text-info fs-1"></i>
                            </div>
                            <h2 class="display-4 fw-bold text-info mb-2" id="activeUsersCount">0</h2>
                            <p class="text-muted mb-0 fw-medium">Usuarios Activos</p>
                            <small class="text-muted">Con tickets recientes</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card card-primary h-100">
                        <div
                            class="card-header card-header-gradient card-header-gradient-primary d-flex justify-content-between align-items-center"
                        >
                            <div>
                                <i class="bi bi-flag-fill me-2"></i>
                                <span class="fw-semibold">Tickets por Prioridad</span>
                            </div>
                            <button
                                class="btn btn-sm btn-outline-dark rounded-pill"
                                data-bs-toggle="tooltip"
                                title="Distribución de tickets según nivel de prioridad"
                            >
                                <i class="bi bi-info-circle"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div
                                class="chart-container position-relative"
                                style="min-height: 200px;"
                            >
                                <canvas id="priorityChart"></canvas>
                                <div
                                    class="no-data-message text-center text-muted position-absolute top-50 start-50 translate-middle d-flex flex-column align-items-center"
                                    style="display: none; font-weight: 600; font-size: 1.2rem; gap: 0.5rem;"
                                >
                                    <i class="bi bi-info-circle" style="font-size: 2.5rem; color: #6c757d;"></i>
                                    No hay datos para mostrar.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card card-danger h-100">
                        <div
                            class="card-header card-header-gradient card-header-gradient-danger d-flex justify-content-between align-items-center"
                        >
                            <div>
                                <i class="bi bi-bug-fill me-2"></i>
                                <span class="fw-semibold">Problemas más Recurrentes</span>
                            </div>
                            <button
                                class="btn btn-sm btn-outline-light rounded-pill"
                                data-bs-toggle="tooltip"
                                title="Tipos de problemas más comunes reportados"
                            >
                                <i class="bi bi-info-circle"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div
                                class="chart-container position-relative"
                                style="min-height: 200px;"
                            >
                                <canvas id="problemsChart"></canvas>
                                <div
                                    class="no-data-message text-center text-muted position-absolute top-50 start-50 translate-middle d-flex flex-column align-items-center"
                                    style="display: none; font-weight: 600; font-size: 1.2rem; gap: 0.5rem;"
                                >
                                    <i class="bi bi-info-circle" style="font-size: 2.5rem; color: #6c757d;"></i>
                                    No hay datos para mostrar.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="card card-success">
                        <div
                            class="card-header card-header-gradient card-header-gradient-success d-flex justify-content-between align-items-center"
                        >
                            <div>
                                <i class="bi bi-calendar-week-fill me-2"></i>
                                <span class="fw-semibold">Evolución de Tickets (Mes Actual)</span>
                            </div>
                            <div>
                                <button
                                    class="btn btn-sm btn-outline-light rounded-pill me-2"
                                >
                                    <i class="bi bi-download me-1"></i> Exportar
                                </button>
                                <button
                                    class="btn btn-sm btn-outline-light rounded-pill"
                                    data-bs-toggle="tooltip"
                                    title="Tickets creados por día en el mes actual"
                                >
                                    <i class="bi bi-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div
                                class="chart-container position-relative"
                                style="height: 350px;"
                            >
                                <canvas id="monthlyChart"></canvas>
                                <div
                                    class="no-data-message text-center text-muted position-absolute top-50 start-50 translate-middle d-flex flex-column align-items-center"
                                    style="display: none; font-weight: 600; font-size: 1.2rem; gap: 0.5rem;"
                                >
                                    <i class="bi bi-info-circle" style="font-size: 2.5rem; color: #6c757d;"></i>
                                    No hay datos para mostrar.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 3 -->
            <div class="row g-4 mt-4">
                <div class="col-lg-12">
                    <div class="card card-warning">
                        <div
                            class="card-header card-header-gradient card-header-gradient-warning d-flex justify-content-between align-items-center"
                        >
                            <div>
                                <i class="bi bi-building-fill me-2"></i>
                                <span class="fw-semibold">Áreas con más Tickets</span>
                            </div>
                            <button
                                class="btn btn-sm btn-outline-dark rounded-pill"
                                data-bs-toggle="tooltip"
                                title="Distribución de tickets por área/departamento"
                            >
                                <i class="bi bi-info-circle"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div
                                class="chart-container position-relative"
                                style="min-height: 200px;"
                            >
                                <canvas id="areasChart"></canvas>
                                <div
                                    class="no-data-message text-center text-muted position-absolute top-50 start-50 translate-middle d-flex flex-column align-items-center"
                                    style="display: none; font-weight: 600; font-size: 1.2rem; gap: 0.5rem;"
                                >
                                    <i class="bi bi-info-circle" style="font-size: 2.5rem; color: #6c757d;"></i>
                                    No hay datos para mostrar.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inicializar tooltips
        document.addEventListener("DOMContentLoaded", function () {
            var tooltipTriggerList = [].slice.call(
                document.querySelectorAll("[data-bs-toggle='tooltip']")
            );
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        function fetchData(type, callback) {
            fetch(`charts_data.php?type=${type}`)
                .then((res) => res.json())
                .then((data) => callback(data))
                .catch((err) => console.error(err));
        }

        // Obtener datos de resumen
        fetch("data/tickets-data.php")
            .then((res) => res.json())
            .then((data) => {
                document.getElementById("openTicketsCount").textContent =
                    data.openTickets || "0";
                document.getElementById("closedTicketsCount").textContent =
                    data.closedTickets || "0";
                document.getElementById("activeUsersCount").textContent =
                    data.activeUsers || "0";
            })
            .catch((err) => console.error(err));

        // Helper para mostrar/ocultar mensaje y canvas según datos
        function toggleNoDataMessage(container, data) {
            const noDataMsg = container.querySelector(".no-data-message");
            const canvas = container.querySelector("canvas");
            if (
                !data ||
                data.length === 0 ||
                data.every((d) => {
                    // Buscar cualquier propiedad con valor numérico para validar
                    return Object.values(d).every((v) => Number(v) === 0);
                })
            ) {
                noDataMsg.style.display = "flex";
                canvas.style.display = "none";
                return false;
            } else {
                noDataMsg.style.display = "none";
                canvas.style.display = "block";
                return true;
            }
        }

        // Chart: Prioridad
        fetchData("priority", (data) => {
            const container = document.querySelector("#priorityChart").parentElement;
            if (!toggleNoDataMessage(container, data)) return;

            new Chart(document.getElementById("priorityChart"), {
                type: "pie",
                data: {
                    labels: data.map((d) => d.priority),
                    datasets: [
                        {
                            data: data.map((d) => d.total),
                            backgroundColor: [
                                "#FF6384",
                                "#36A2EB",
                                "#FFCE56",
                                "#4BC0C0",
                                "#9966FF",
                                "#FF9F40",
                            ],
                            borderWidth: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "right",
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: "circle",
                            },
                        },
                    },
                    cutout: "60%",
                },
            });
        });

        // Chart: Problemas
        fetchData("problems", (data) => {
            const container = document.querySelector("#problemsChart").parentElement;
            if (!toggleNoDataMessage(container, data)) return;

            new Chart(document.getElementById("problemsChart"), {
                type: "bar",
                data: {
                    labels: data.map((d) => d.problem_type),
                    datasets: [
                        {
                            label: "Cantidad",
                            data: data.map((d) => d.total),
                            backgroundColor: "#FF5733",
                            borderRadius: 6,
                            borderWidth: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                            },
                        },
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                },
            });
        });

        // Chart: Conteo mensual
        fetchData("monthly_count", (data) => {
            const container = document.querySelector("#monthlyChart").parentElement;
            if (!toggleNoDataMessage(container, data)) return;

            new Chart(document.getElementById("monthlyChart"), {
                type: "line",
                data: {
                    labels: data.map((d) => d.fecha),
                    datasets: [
                        {
                            label: "Tickets por Día",
                            data: data.map((d) => d.total),
                            borderColor: "#28A745",
                            backgroundColor: "rgba(40, 167, 69, 0.1)",
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: "#fff",
                            pointBorderColor: "#28A745",
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                            },
                        },
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                },
            });
        });

        // Chart: Áreas
        fetchData("areas", (data) => {
            const container = document.querySelector("#areasChart").parentElement;
            if (!toggleNoDataMessage(container, data)) return;

            new Chart(document.getElementById("areasChart"), {
                type: "doughnut",
                data: {
                    labels: data.map((d) => d.area),
                    datasets: [
                        {
                            data: data.map((d) => d.total),
                            backgroundColor: [
                                "#FFC107",
                                "#17A2B8",
                                "#6F42C1",
                                "#FD7E14",
                                "#20C997",
                                "#DC3545",
                            ],
                            borderWidth: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "right",
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: "circle",
                            },
                        },
                    },
                    cutout: "60%",
                },
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
