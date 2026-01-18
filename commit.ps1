param(
    [string]$Message = "update",
    [string]$Branch = ""
)

if ($Branch -ne "") {
    git checkout $Branch
}

git status
git add -A

git commit -m $Message

git push

