document.querySelector("form").addEventListener("submit", function () {
  const input = document.querySelector("input[name='query']").value;
  if (input.trim() === "") {
    alert("Please enter a search keyword");
  }
});