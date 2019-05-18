#!/bin/sh


echo "TRAVIS_PULL_REQUEST=$TRAVIS_PULL_REQUEST"
echo "TRAVIS_BRANCH=$TRAVIS_BRANCH"
echo "TRAVIS_PULL_REQUEST_BRANCH=$TRAVIS_PULL_REQUEST_BRANCH"
echo "TRAVIS_PULL_REQUEST_SLUG=$TRAVIS_PULL_REQUEST_SLUG"
echo "PR=$PR"
echo "BRANCH=$BRANCH"
echo "TRAVIS_COMMIT=$TRAVIS_COMMIT"
echo "TRAVIS_COMMIT_RANGE=$TRAVIS_COMMIT_RANGE"
echo "TRAVIS_REPO_SLUG=$TRAVIS_REPO_SLUG"
echo "HOME=$HOME"
echo "TRAVIS_ALLOW_FAILURE=$TRAVIS_ALLOW_FAILURE"



export BRANCH=$(if [ "$TRAVIS_PULL_REQUEST" == "false" ]; then echo $TRAVIS_BRANCH; else echo $TRAVIS_PULL_REQUEST_BRANCH; fi)

git config --global user.email "travis@travis-ci.org"
git config --global user.name "Travis CI"


git checkout -b $BRANCH
git add --all
git commit --message "Travis build: $TRAVIS_BUILD_NUMBER"

git remote add repo-source https://${GH_TOKEN}@github.com/$TRAVIS_PULL_REQUEST_SLUG.git > /dev/null 2>&1
git push --quiet --set-upstream repo-source $BRANCH