#!/usr/bin/env python

import srt
import pprint
import imp
import re
import os
import inspect

import urllib, json
import sys
from subprocess import call


def DisplayFolderInfo(folder, displayShift):
    path = folder.GetName()
    print(displayShift + "- " + path)
    clips = folder.GetClips()
    for clipIndex in clips:


        clip = clips[clipIndex]
        file_name = clip.GetClipProperty("File Name")["File Name"]


        prop = clip.GetClipProperty()
        print("Got a clip!")
        x = dir(clip)
        pprint.pprint(x)

        if 'MOV' in file_name:

            frames = int(prop['Frames']);

            # pprint.pprint(prop)
            for key, value in prop.items():
                if value:
                    print key.rjust(30), value

                # print key.rjust(10), value

#             return
            # AddMarkers(clip, displayShift)


            markers = clip.GetMarkers()
            for key, value in markers.iteritems():
                # clip.RemoveMarker(value)
                pprint.pprint(value)


            # break
        # print clip.GetMediaId()




    displayShift = "  " + displayShift

    folders = folder.GetSubFolders()
    for folderIndex in folders:
        DisplayFolderInfo(folders[folderIndex], displayShift)
    return


def DisplayMediaPoolInfo(project):
    mediaPool = project.GetMediaPool()
    print("- Media pool")
    DisplayFolderInfo(mediaPool.GetRootFolder(), "  ")
    return


# print "Number of arguments: ", len(sys.argv)
projectCode = sys.argv[1]
projectName = projectCode + "-test"

# call(["php", 'C:\Users\tacma\github\resolve-transcript\bin\console app:export-fcp ', projectCode,' --max 4'])
cmd = 'php C:\\Users\\tacma\\github\\resolve-transcript\\bin\\console app:export-fcp ' + projectCode
print cmd
os.system(cmd)


# smodule = imp.load_dynamic('fusionscript', 'C:\\Program Files\\Blackmagic Design\\DaVinci Resolve\\fusionscript.dll')
smodule = imp.load_dynamic('fusionscript', '/mnt/c/Program Files/Blackmagic Design/DaVinci Resolve/fusionscript.dll')
resolve = smodule.scriptapp('Resolve')

projectManager = resolve.GetProjectManager()
current = projectManager.GetCurrentProject()
if current:
    projectManager.SaveProject()

mediaStorage = resolve.GetMediaStorage()

# get these from the database, or via twig

# create the project if it doesn't exist
project = projectManager.LoadProject(projectName)
if project is None:
    project = projectManager.CreateProject(projectName)


if project is None:
    raise ValueError('Unable to load / create ' + projectName)


# go through all the items in the media pool, not just the new ones
mediaPool = project.GetMediaPool()
filePath = "C:\\JUFJ\\temp\\" + projectCode + ".fcpxml"
timeline = mediaPool.ImportTimelineFromFile(filePath)

projectManager.SaveProject()

exit(0)


clips = mediaStorage.AddItemsToMediaPool([
    "C:\\JUFJ\Kesh\kesh-2e.MOV"
    "C:\\JUFJ\music\\bensound-jazzyfrenchy.mp3"
])


# Master Pool?
folder = mediaPool.GetRootFolder()

# go through each clip and add the markers
clips = folder.GetClips()

for clipIndex in clips:

    clip = clips[clipIndex]

    # remove previous markers
    markers = clip.GetMarkers()
    for markerIndex in markers:
        marker = markers[markerIndex]
        print marker
        print clip.GetClipProperty()
        # clip.DeleteMarker(marker)

    file_name = clip.GetClipProperty("File Path")["File Path"]
    # pprint.pprint(clip.GetClipProperty);

    AddMarkers(clip, '')

timeline = mediaPool.CreateEmptyTimeline('markers')
print(timeline)

for markerIndex in markers:
    print "Adding Marker to timeline"
    marker = markers[markerIndex]
    # mediaPool.AppendToTimeline(marker)
    dir(marker)
    pprint.pprint(marker)

project = projectManager.GetCurrentProject()





# Get each clip in the Media Pool
# project = projectManager.GetCurrentProject()
# DisplayMediaPoolInfo(project)


