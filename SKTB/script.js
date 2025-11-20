new Chart(document.getElementById("chartPemesanan"), {
    type: 'doughnut',
    data: {
        labels: ["Fresh Flowers", "Pipe Cleaner", "Lainnya"],
        datasets: [{
            data: [38, 22.7, 49.3],
            backgroundColor: ["#f8bbd0", "#c2185b", "#ad1457"]
        }]
    }
});