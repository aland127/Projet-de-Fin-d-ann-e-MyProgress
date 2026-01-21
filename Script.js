// ----------------------
// Connexion
// ----------------------
const emailConnexion = document.getElementById("email");
const mdpConnexion = document.getElementById("motdepasse");
const boutonConnexion = document.getElementById("bouton-connexion");

const erreurEmailConnexion = document.getElementById("erreur-email");
const erreurMdpConnexion = document.getElementById("erreur-mdp");

if (emailConnexion && mdpConnexion && boutonConnexion) {
  function verifierConnexion() {
    const emailValide = emailConnexion.value.trim() !== "";
    const mdpValide = mdpConnexion.value.trim() !== "";

    erreurEmailConnexion.style.display = emailValide ? "none" : "block";
    erreurMdpConnexion.style.display = mdpValide ? "none" : "block";

    boutonConnexion.disabled = !(emailValide && mdpValide);
  }

  emailConnexion.addEventListener("input", verifierConnexion);
  mdpConnexion.addEventListener("input", verifierConnexion);
}


// ----------------------
// Inscription
// ----------------------
document.addEventListener("DOMContentLoaded", () => {
  const formulaire = document.getElementById("form-inscription");
  const bouton = document.getElementById("bouton-inscription");

  const champs = [
    "prenom", "nom", "age", "poids", "email", "motdepasse", "confirmation"
  ];

  const etatChamps = {
    prenom: false,
    nom: false,
    age: false,
    poids: false,
    email: false,
    motdepasse: false,
    confirmation: false
  };

  champs.forEach(id => {
    const champ = document.getElementById(id);
    champ.addEventListener("input", () => {
      etatChamps[id] = champ.value.trim() !== "";

      if (id === "confirmation") {
        const mdp = document.getElementById("motdepasse").value;
        etatChamps.confirmation = (champ.value === mdp);
        document.getElementById("erreur-confirmation").style.display = etatChamps.confirmation ? "none" : "block";
      }

      bouton.disabled = Object.values(etatChamps).includes(false);
    });
  });

  formulaire.addEventListener("submit", (e) => {
    const mdp = document.getElementById("motdepasse").value;
    const confirm = document.getElementById("confirmation").value;

    if (mdp !== confirm) {
      e.preventDefault();
      alert("Les mots de passe ne correspondent pas.");
    }
  });
});


// ----------------------
// Ajouter une séance
// ----------------------
const selectType = document.getElementById("type");
const blocMuscu = document.getElementById("bloc-musculation");
const blocCardio = document.getElementById("bloc-cardio");
const blocVelo = document.getElementById("bloc-velo");
const blocNatation = document.getElementById("bloc-natation");
const selectZones = document.getElementById("zones");
const conteneurExercices = document.getElementById("conteneur-exercices");

const exercicesMuscu = {
  bras: ["Curl haltères en alterné", "Curl barre EZ", "Curl incliné sur banc", "Curl à la poulie basse", "Curl concentré", "Extensions à la poulie haute", "Dips", "Barre front (skullcrusher)", "Extensions haltère derrière la tête", "Poulie unilatérale triceps"],
  jambes: ["Squat à la barre", "Presse à jambes", "Fentes marchées ou statiques", "Hack squat", "Extension des jambes", "Soulevé de terre jambes tendues", "Leg curl allongé ou assis", "Hip thrust", "Mollets à la presse ou debout à la barre", "Step-up sur banc"],
  pectoraux: ["Développé couché barre", "Développé incliné haltères", "Développé décliné", "Pompes", "Dips", "Écarté couché haltères", "Pec deck", "Écarté poulie vis-à-vis", "Poulie basse en vis-à-vis", "Squeeze press"],
  dos: ["Rowing barre", "Rowing haltères à un bras", "Rowing à la machine", "Rowing poulie basse", "Rowing T-bar", "Tractions pronation", "Tirage vertical poulie", "Tirage vertical prise neutre", "Pull-over à la poulie", "Extensions lombaires"],
  epaules: ["Développé militaire barre", "Développé haltères assis", "Développé Arnold", "Machine à épaules", "Élévations latérales haltères", "Élévations poulie basse", "Oiseau", "Reverse pec deck", "Face pull", "Élévations frontales"],
  abdominaux: ["Crunch au sol", "Crunch poulie haute", "Sit-ups", "Relevés jambes au sol", "Relevés jambes chaise romaine", "Reverse crunch", "Russian twists", "Gainage latéral", "Planche", "Hollow body hold"]
};

let selectExosMuscu = null;
let inputAutreExoMuscu = null;
let champsPoids = [];

function afficherBlocConditionnel(type) {
  [blocMuscu, blocCardio, blocVelo, blocNatation].forEach(bloc => {
    if (bloc) bloc.classList.add("masque");
  });

  switch (type) {
    case "musculation": blocMuscu.classList.remove("masque"); break;
    case "cardio": blocCardio.classList.remove("masque"); break;
    case "vélo": blocVelo.classList.remove("masque"); break;
    case "natation": blocNatation.classList.remove("masque"); break;
  }
}

if (selectType) {
  selectType.addEventListener("change", function () {
    afficherBlocConditionnel(this.value);
  });
}

if (selectZones) {
  selectZones.addEventListener("change", function () {
    conteneurExercices.innerHTML = "";
    champsPoids = [];

    const zones = Array.from(this.selectedOptions).map(opt => opt.value);

    zones.forEach(zone => {
      const label = document.createElement("label");
      label.textContent = `Exercices pour ${zone} :`;
      label.style.display = "block";
      label.style.marginTop = "1rem";
      conteneurExercices.appendChild(label);

      const select = document.createElement("select");
      select.name = `exercices_${zone}[]`;
      select.multiple = true;
      select.style.marginBottom = "0.5rem";
      select.style.width = "100%";

      exercicesMuscu[zone].forEach(exo => {
        const option = document.createElement("option");
        option.value = exo;
        option.textContent = exo;
        select.appendChild(option);
      });

      conteneurExercices.appendChild(select);


      select.addEventListener("change", function () {
        champsPoids
          .filter(el => el.dataset.zone === zone)
          .forEach(el => el.remove());
        champsPoids = champsPoids.filter(el => el.dataset.zone !== zone);

        Array.from(select.selectedOptions).forEach(opt => {
          const input = document.createElement("input");
          input.type = "number";
          const exoClean = opt.value.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/\s+/g, "_");
          input.name = `poids_${zone}_${exoClean}`;
          input.placeholder = `Poids pour ${opt.value} (kg)`;
          input.min = "0";
          input.step = "0.5";
          input.dataset.zone = zone;
          input.style.marginBottom = "0.5rem";
          input.style.width = "100%";
          conteneurExercices.appendChild(input);
          champsPoids.push(input);

          const inputReps = document.createElement("input");
inputReps.type = "number";
inputReps.name = `repetitions_${zone}_${exoClean}`;
inputReps.placeholder = `Répétitions pour ${opt.value}`;
inputReps.min = "1";
inputReps.step = "1";
inputReps.dataset.zone = zone;
inputReps.style.marginBottom = "1rem";
inputReps.style.width = "100%";
conteneurExercices.appendChild(inputReps);
champsPoids.push(inputReps); 

        });
      });
    });
  });
}

// ----------------------
// Historique 
// ----------------------
// ----------------------
// Chargement des séances + suppression
// ----------------------
document.addEventListener("DOMContentLoaded", () => {
  const liste = document.getElementById("liste-seances");
  const selectTriDate = document.getElementById("tri-date");
  const filtreType = document.getElementById("filtre-type");
  const filtreIntensite = document.getElementById("filtre-intensite");

  function normaliserTexte(texte) {
    return texte.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
  }

  function appliquerFiltres() {
    const typeChoisi = filtreType.value;
    const intensiteChoisie = filtreIntensite.value;
    const toutesLesCartes = document.querySelectorAll("#liste-seances .carte-seance");

    toutesLesCartes.forEach(carte => {
      const titre = carte.querySelector("h3").textContent;
      const intensiteTexte = carte.querySelectorAll(".seance-detail");
      let intensite = "";

      intensiteTexte.forEach(p => {
        if (p.textContent.toLowerCase().startsWith("intensité")) {
          intensite = p.textContent.split(":")[1].trim();
        }
      });

      const correspondType = !typeChoisi || normaliserTexte(titre).includes(normaliserTexte(typeChoisi));
      const correspondIntensite = !intensiteChoisie || normaliserTexte(intensite) === normaliserTexte(intensiteChoisie);

      carte.style.display = (correspondType && correspondIntensite) ? "block" : "none";
    });
  }

  fetch("get_seances.php")
    .then(res => res.json())
    .then(seances => {
      liste.innerHTML = "";

      seances.forEach(seance => {
        const div = document.createElement("div");
        div.className = "carte-seance";
        div.dataset.id = seance.id;
        div.dataset.date = seance.date;

        const h3 = document.createElement("h3");
        h3.textContent = `${seance.type} – ${new Date(seance.date).toLocaleDateString("fr-FR")}`;
        div.appendChild(h3);

        const pIntensite = document.createElement("p");
        pIntensite.className = "seance-detail";
        pIntensite.textContent = `Intensité : ${seance.intensite}`;
        div.appendChild(pIntensite);

        const pFatigue = document.createElement("p");
        pFatigue.className = "seance-detail";
        pFatigue.textContent = `Fatigue : ${seance.fatigue}`;
        div.appendChild(pFatigue);

        if (seance.exercices.length > 0) {
          const blocExos = document.createElement("div");
          blocExos.className = "sous-section";
          const titre = document.createElement("strong");
          titre.textContent = "Exercices :";
          blocExos.appendChild(titre);

          const ul = document.createElement("ul");
         seance.exercices.forEach(ex => {
  const li = document.createElement("li");

  let info = "";

if (ex.poids != null && ex.poids !== "") {
  info += ` – ${ex.poids} kg`;
}
if (ex.repetitions != null && ex.repetitions !== "") {
  info += ` – ${ex.repetitions} répétitions`;
}
if (ex.distance != null && ex.distance !== "" && !isNaN(ex.distance)) {
  const unit = seance.type === "natation" ? "m" : "km";
  info += ` – ${ex.distance} ${unit}`;
}
if (ex.vitesse != null && ex.vitesse !== "" && !isNaN(ex.vitesse)) {
  info += ` – ${ex.vitesse} km/h`;
}





  li.textContent = `${ex.zone ? `[${ex.zone}] ` : ""}${ex.exercice || ""}${info}`;
  ul.appendChild(li);
});


          blocExos.appendChild(ul);
          div.appendChild(blocExos);
        }

        const blocCom = document.createElement("div");
        blocCom.className = "sous-section";
        const titreCom = document.createElement("strong");
        titreCom.textContent = "Commentaire :";
        blocCom.appendChild(titreCom);
        const texteCom = document.createElement("span");
        texteCom.textContent = ` ${seance.commentaire || ""}`;
        blocCom.appendChild(texteCom);
        div.appendChild(blocCom);

        const actions = document.createElement("div");
        actions.className = "sous-section";
        actions.style = "display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;";

        const btnSupprimer = document.createElement("button");
        btnSupprimer.className = "supprimer-seance";
        btnSupprimer.textContent = "Supprimer";
        btnSupprimer.addEventListener("click", () => {
  if (confirm("Supprimer cette séance ?")) {
    const id = div.dataset.id; 
    fetch(`delete_seance.php?id=${id}`, { method: "GET" })
      .then(res => res.text())
      .then(msg => {
        console.log(msg);
        div.remove();
      })
      .catch(err => console.error(err));
  }
});


        actions.appendChild(btnSupprimer);
        div.appendChild(actions);

        liste.appendChild(div);
      });

      if (selectTriDate) {
        selectTriDate.addEventListener("change", () => {
          const cartes = Array.from(liste.querySelectorAll(".carte-seance"));
          cartes.sort((a, b) => new Date(a.dataset.date) - new Date(b.dataset.date));
          if (selectTriDate.value === "desc") cartes.reverse();
          cartes.forEach(carte => liste.appendChild(carte));
        });
        selectTriDate.dispatchEvent(new Event("change"));
      }

      if (filtreType) filtreType.addEventListener("change", appliquerFiltres);
      if (filtreIntensite) filtreIntensite.addEventListener("change", appliquerFiltres);
    })
    .catch(err => {
      liste.innerHTML = "<p>Erreur lors du chargement des séances.</p>";
      console.error(err);
    });
});





// ----------------------
// Objectifs
// ----------------------
document.addEventListener("DOMContentLoaded", () => {
  const liste = document.getElementById("liste-objectifs");
  const btnAjouter = document.getElementById("btn-ajouter-objectif");
  const formBloc = document.getElementById("form-ajout");
  const form = document.getElementById("formulaire-objectif");
  const selectType = document.getElementById("type_obj");
  const selectExo = document.getElementById("exercice_obj");
  const blocExercice = document.getElementById("bloc-exercice");
  const frequenceContainer = document.getElementById("frequence-container");

  const exercicesMuscu = {
    bras: ["Curl haltères en alterné", "Curl barre EZ", "Curl incliné sur banc", "Curl à la poulie basse", "Curl concentré", "Extensions à la poulie haute", "Dips", "Barre front (skullcrusher)", "Extensions haltère derrière la tête", "Poulie unilatérale triceps"],
    jambes: ["Squat à la barre", "Presse à jambes", "Fentes marchées ou statiques", "Hack squat", "Extension des jambes", "Soulevé de terre jambes tendues", "Leg curl allongé ou assis", "Hip thrust", "Mollets à la presse ou debout à la barre", "Step-up sur banc"],
    pectoraux: ["Développé couché barre", "Développé incliné haltères", "Développé décliné", "Pompes", "Dips", "Écarté couché haltères", "Pec deck", "Écarté poulie vis-à-vis", "Poulie basse en vis-à-vis", "Squeeze press"],
    dos: ["Rowing barre", "Rowing haltères à un bras", "Rowing à la machine", "Rowing poulie basse", "Rowing T-bar", "Tractions pronation", "Tirage vertical poulie", "Tirage vertical prise neutre", "Pull-over à la poulie", "Extensions lombaires"],
    epaules: ["Développé militaire barre", "Développé haltères assis", "Développé Arnold", "Machine à épaules", "Élévations latérales haltères", "Élévations poulie basse", "Oiseau", "Reverse pec deck", "Face pull", "Élévations frontales"],
    abdominaux: ["Crunch au sol", "Crunch poulie haute", "Sit-ups", "Relevés jambes au sol", "Relevés jambes chaise romaine", "Reverse crunch", "Russian twists", "Gainage latéral", "Planche", "Hollow body hold"]
  };

  const exercicesCardio = ["Course à pied", "Corde à sauter", "Rameur", "Burpees", "Montées de genoux", "Escaliers", "Fractionné", "Sauts en étoile", "Jumping jacks", "Autre"];
  const exercicesVelo = ["Sprint", "Côte", "Endurance", "Fractionné", "Autre"];
  const exercicesNatation = ["Brasse", "Crawl", "Papillon", "Dos crawlé", "Battements avec planche", "Autre"];

  function updateAffichageEtExercices(type) {
    if (type === "nombre-seances") {
      frequenceContainer.style.display = "block";
      blocExercice.style.display = "none";
      selectExo.innerHTML = "";
    } else {
      frequenceContainer.style.display = "none";
      blocExercice.style.display = "block";
      remplirExercices(type);
    }
  }

  function remplirExercices(type) {
  selectExo.innerHTML = "";
  const defaultOpt = document.createElement("option");
  defaultOpt.value = "";
  defaultOpt.textContent = "— Aucun —";
  selectExo.appendChild(defaultOpt);

  if (type === "musculation") {
    for (const zone in exercicesMuscu) {
      const group = document.createElement("optgroup");
      group.label = zone;
      exercicesMuscu[zone].forEach(ex => {
        const opt = document.createElement("option");
        opt.value = ex;
        opt.textContent = ex;
        group.appendChild(opt);
      });
      selectExo.appendChild(group);
    }
  } else if (type === "cardio") {
    exercicesCardio.forEach(ex => {
      const opt = document.createElement("option");
      opt.value = ex;
      opt.textContent = ex;
      selectExo.appendChild(opt);
    });
  } else if (type === "velo") {
    exercicesVelo.forEach(ex => {
      const opt = document.createElement("option");
      opt.value = ex;
      opt.textContent = ex;
      selectExo.appendChild(opt);
    });
  } else if (type === "natation") {
    exercicesNatation.forEach(ex => {
      const opt = document.createElement("option");
      opt.value = ex;
      opt.textContent = ex;
      selectExo.appendChild(opt);
    });
  }
}


  // Initialisation
  updateAffichageEtExercices(selectType.value);

  selectType.addEventListener("change", () => {
    updateAffichageEtExercices(selectType.value);
  });

  btnAjouter.addEventListener("click", () => {
    formBloc.classList.remove("masque");
    btnAjouter.classList.add("masque");
  });

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    fetch("ajouter_objectif.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.text())
      .then(msg => {
        alert(msg);
        location.reload();
      })
      .catch(err => {
        alert("Erreur lors de l'ajout.");
        console.error(err);
      });
  });

  // Chargement des objectifs
  fetch("get_objectifs.php")
    .then(res => res.json())
    .then(data => {
      liste.innerHTML = "";
      if (data.length === 0) {
        liste.innerHTML = "<p>Aucun objectif pour l’instant.</p>";
      } else {
        data.forEach(obj => {
  const carte = document.createElement("div");
  carte.className = "carte-objectif";
  carte.dataset.id = obj.id;

  const progression = obj.valeur_actuelle && obj.valeur_objectif
    ? Math.min(100, Math.round((obj.valeur_actuelle / obj.valeur_objectif) * 100))
    : 0;


 let titre = "";
if (obj.exercice && obj.exercice.trim() !== "" && obj.exercice.toLowerCase() !== "aucun") {
  titre = obj.exercice;
} else if (obj.type_affiche && obj.type_affiche.trim() !== "") {
  titre = obj.type_affiche;
} else if (obj.type && obj.type.trim() !== "") {
  titre = obj.type;
} else {
  titre = "Objectif";
}


  carte.innerHTML = `
  <h3>${titre}</h3>
  <p><strong>${obj.valeur_actuelle ?? 0}</strong> / ${obj.valeur_objectif} ${obj.unite || ""}</p>
  <div class="progression">
    <div class="progression-barre" style="width: ${progression}%; background-color: ${
      progression >= 100 ? 'green' : progression > 0 ? 'orange' : '#1e90ff'
    }"></div>
  </div>
  <p>Statut : <span class="statut ${
    progression >= 100 ? "ok" : progression > 0 ? "moyen" : "alerte"
  }">${progression >= 100 ? "Atteint" : progression > 0 ? "En cours" : "Non commencé"}</span></p>
`;


  liste.appendChild(carte);
  const btnSupprimer = document.createElement("button");
btnSupprimer.textContent = "Supprimer";
btnSupprimer.className = "bouton-supprimer";

btnSupprimer.addEventListener("click", () => {
  const id = carte.dataset.id;
  if (confirm("Supprimer cet objectif ?")) {
    fetch(`supprimer_objectif.php?id=${id}`)
      .then(res => res.text())
      .then(msg => {
        alert(msg);
        carte.remove();
      })
      .catch(err => console.error(err));
  }
});

carte.appendChild(btnSupprimer);


});

      }
    })
    .catch(err => {
      console.error(err);
      liste.innerHTML = "<p>Erreur de chargement.</p>";
    });
});











