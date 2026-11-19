function init() {
    document.getElementById("hasil").innerHTML = "0";
}

function hitungKurs(id) {

    var rupiah = parseFloat(document.getElementById(id).value);

    if (isNaN(rupiah) || rupiah < 0) {
        alert("Masukkan nominal Rupiah yang benar!");
        return;
    }

    var kurs = 18246;

    var dollar = rupiah / kurs;

    document.getElementById("hasil").innerHTML = dollar.toFixed(2);

    var li = document.createElement("li");

    var teks = document.createTextNode("Rp" + rupiah + " = US$" + dollar.toFixed(2));

    li.appendChild(teks);

    document.getElementById("riwayat").appendChild(li);
}

