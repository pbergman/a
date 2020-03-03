export FOO=$(
    sleep 10 &
    FOO=$!
)
echo $FOO