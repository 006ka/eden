// Versets du jour (exemple simple)
const versets = [
    "Psaume 23:1 - L'Éternel est mon berger: je ne manquerai de rien.",
    "Philippiens 4:13 - Je puis tout par celui qui me fortifie.",
    "Jean 8:12 - Je suis la lumière du monde.",
    "Psaume 91:11 - Il ordonnera à ses anges de te garder."
];

document.getElementById("verset").textContent = 
    versets[Math.floor(Math.random() * versets.length)];
