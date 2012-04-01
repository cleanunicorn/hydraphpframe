#!/bin/bash
chmod 777 templates_c/
chmod 777 templates_cache/
svn add templates/*
svn add lib/*
svn add src/*
svn add handjobs/*
svn add cronjobs/*
svn add admin/*
svn add js/*
svn add images/*

