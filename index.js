const perPage = 50;
let allResults = {};
let currentPage = {};

async function findWords() {
  const rack = document.getElementById("rackInput").value.trim().toLowerCase();
  if (!rack) {
    alert("Please enter some letters.");
    return;
  }

  const response = await fetch("scrabble_word_finder.php?rack=" + encodeURIComponent(rack));
  if (!response.ok) {
    alert("Error: " + response.status);
    return;
  }
  const data = await response.json();
  allResults = data.results;
  currentPage = {};
  renderResults();
}

function renderResults() {
  const container = document.getElementById("results-container");
  container.innerHTML = "";

  const lengths = Object.keys(allResults).sort((a, b) => b - a);
  if (lengths.length === 0) {
    container.innerHTML = "<p style='text-align:center;'>No results found.</p>";
    return;
  }

  lengths.forEach(len => {
    const words = allResults[len];
    if (words.length === 0) return;

    const button = document.createElement("button");
    button.className = "collapsible";
    button.textContent = `${len}-Letter Words (${words.length})`;

    const contentDiv = document.createElement("div");
    contentDiv.className = "content";

    const paginationDiv = document.createElement("div");
    paginationDiv.className = "pagination";
    contentDiv.appendChild(paginationDiv);

    container.appendChild(button);
    container.appendChild(contentDiv);

    // Start collapsed on mobile
    contentDiv.style.maxHeight = "0px";

    button.onclick = function() {
      const isActive = this.classList.toggle("active");
      if (isActive) {
        showPage(contentDiv, len, 1);
        contentDiv.style.maxHeight = contentDiv.scrollHeight + "px";
      } else {
        contentDiv.style.maxHeight = "0px";
      }
    };

    // Optional: close sections if screen width < 480px on load
    if (window.innerWidth <= 480) {
      contentDiv.style.maxHeight = "0px";
      button.classList.remove("active");
    }
  });
}

function showPage(contentDiv, length, page) {
  const words = allResults[length];
  const paginationDiv = contentDiv.querySelector('.pagination');

  // Clear previous content
  contentDiv.querySelectorAll('.word-card').forEach(el => el.remove());
  paginationDiv.innerHTML = "";

  const offset = (page - 1) * perPage;
  const paginatedWords = words.slice(offset, offset + perPage);

  if (paginatedWords.length === 0) {
    const emptyDiv = document.createElement("div");
    emptyDiv.textContent = "No results found.";
    emptyDiv.style.textAlign = "center";
    contentDiv.appendChild(emptyDiv);
  } else {
    paginatedWords.forEach(item => {
      const card = document.createElement("div");
      card.className = "word-card";
      card.innerHTML = `<span>${item.word}</span><span>${item.length}</span><span>${item.score}</span>`;
      contentDiv.insertBefore(card, paginationDiv);
    });
  }

  currentPage[length] = page;
  renderPagination(paginationDiv, length, page, words.length);

  // Smoothly adjust max-height to fit content
  contentDiv.style.maxHeight = contentDiv.scrollHeight + "px";
}

function renderPagination(container, length, page, totalWords) {
  const totalPages = Math.ceil(totalWords / perPage);
  if (totalPages <= 1) return;

  const prevBtn = document.createElement("button");
  prevBtn.textContent = "Prev";
  prevBtn.disabled = page === 1;
  prevBtn.onclick = () => showPage(container.parentElement, length, page - 1);
  container.appendChild(prevBtn);

  const maxButtons = 10;
  let start = Math.max(1, page - Math.floor(maxButtons / 2));
  let end = Math.min(totalPages, start + maxButtons - 1);

  if (end - start < maxButtons - 1) start = Math.max(1, end - maxButtons + 1);

  for (let i = start; i <= end; i++) {
    const btn = document.createElement("button");
    btn.textContent = i;
    if (i === page) btn.classList.add("active");
    btn.onclick = () => showPage(container.parentElement, length, i);
    container.appendChild(btn);
  }

  const nextBtn = document.createElement("button");
  nextBtn.textContent = "Next";
  nextBtn.disabled = page === totalPages;
  nextBtn.onclick = () => showPage(container.parentElement, length, page + 1);
  container.appendChild(nextBtn);
}

document.getElementById("rackInput").addEventListener("keypress", function(event) {
  if (event.key === "Enter") {
    event.preventDefault();
    findWords();
  }
});
