#!/bin/bash

# Function to get the installed version of strauss
get_installed_version() {
    local version_output
    version_output=$(php ./bin/strauss.phar --version)
    echo "$version_output" | sed -n -e 's/^.*strauss //p'
}

# Function to check if the latest release version is not installed
is_update_needed() {
    local latest_release=$1
    local current_version

    if [[ ! -f ./bin/strauss.phar ]]; then
        return 0
    fi

    current_version=$(get_installed_version)
    [[ "$current_version" != "$latest_release" ]]
}

# Function to download and install the latest release
download_and_install() {
    local latest_release=$1
    rm -f ./bin/strauss.phar
    curl -o bin/strauss.phar -L -C - https://github.com/BrianHenryIE/strauss/releases/download/"$latest_release"/strauss.phar
    echo "$latest_release" > ./bin/strauss-version.txt
}

# Main script execution
main() {
    local latest_release
    latest_release="0.20.0" # strauss release version
    if is_update_needed "$latest_release"; then
        echo "Updating strauss to $latest_release ..."
        download_and_install "$latest_release"
    fi
}

main
